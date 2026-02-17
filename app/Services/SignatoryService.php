<?php

namespace App\Services;

use App\Models\UnitKerja;
use App\Models\User;
use App\Models\DailyReportResponse;
use Illuminate\Support\Collection;

/**
 * Service to pick signatory users (pengumpul data + validator/PIC) for a UnitKerja.
 *
 * Usage:
 *   $signatories = (new SignatoryService())->pickForUnit($unitKerja, $entriesOptional);
 *   returns ['pengumpul' => User|null, 'validator' => User|null, 'unit_users' => Collection]
 */
class SignatoryService
{
    /**
     * Pick signatory users for the given unit. If $entries is provided the method will
     * prefer users who actually submitted/validated during that period; otherwise it
     * falls back to users belonging to the unit and their roles.
     *
     * @param UnitKerja $unit
     * @param Collection|null $entries  Collection of DailyReportResponse (optional)
     * @return array{pengumpul: ?User, validator: ?User, unit_users: Collection}
     */

    /**
     * Resolve TTD URL for a user with S3-first but safe fallback to local `public` disk.
     * - If user->ttd_url is already an absolute URL it will be returned as-is.
     * - If S3 is reachable and file exists on S3 -> return S3 URL.
     * - If S3 returns 404 / unreachable -> use `public` disk if file exists.
     * - Returns relative `/storage/...` path for public disk files (safer for headless PDF).
     *
     * @param \App\Models\User $user
     * @return string|null
     */
    public function getTtdUrl(\App\Models\User $user): ?string
    {
        if (! $user || ! $user->ttd_url) {
            return null;
        }

        // If already an absolute URL (e.g. external S3 URL) — return as-is
        if (preg_match('#^https?://#i', $user->ttd_url)) {
            return trim($user->ttd_url);
        }

        // 1) Try S3 (may throw if not reachable) — prefer actual existence check
        try {
            if (\Illuminate\Support\Facades\Storage::disk('s3')->exists($user->ttd_url)) {
                return trim(\Illuminate\Support\Facades\Storage::disk('s3')->url($user->ttd_url));
            }
        } catch (\Throwable $e) {
            // ignore and fallback to public
        }

        // 2) Public/local fallback
        try {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($user->ttd_url)) {
                $rawPublicUrl = trim(\Illuminate\Support\Facades\Storage::disk('public')->url($user->ttd_url));
                $pathOnly = parse_url($rawPublicUrl, PHP_URL_PATH) ?: $rawPublicUrl;
                return '/' . ltrim($pathOnly, '/');
            }
        } catch (\Throwable $e) {
            // nothing else to do
        }

        return null;
    }
    public function pickForUnit(UnitKerja $unit, ?Collection $entries = null): array
    {
        // Precompute counts from $entries (if available)
        $submissionCounts = [];
        $validationCounts = [];
        $submitterIds = [];
        $validatorIds = [];

        if ($entries && $entries->count()) {
            $groupedSubmitted = $entries->groupBy('submitted_by')->map->count();
            $submissionCounts = $groupedSubmitted->toArray();
            $submitterIds = array_map('intval', array_keys($submissionCounts));

            $groupedValidated = $entries->groupBy('validated_by')->map->count();
            $validationCounts = $groupedValidated->toArray();
            $validatorIds = array_map('intval', array_keys($validationCounts));
        }

        // Unit users (active)
        $unitUsers = User::with('roles', 'unitKerjas')
            ->where('status', 'active')
            ->whereHas('unitKerjas', function ($q) use ($unit) {
                $q->where('unit_kerja.id', $unit->id);
            })
            ->get();

        // Submitter candidates (from entries) - prefer those attached to unit
        $submitterCandidates = collect();
        if (!empty($submitterIds)) {
            $submitterCandidates = User::with('roles', 'unitKerjas')
                ->whereIn('id', $submitterIds)
                ->get()
                ->sortByDesc(fn($u) => $u->unitKerjas->pluck('id')->contains($unit->id) ? 1 : 0);
        }

        // Pengumpul role candidates from unit
        $pengumpulRoleCandidates = $unitUsers->filter(fn($u) => $u->roles->pluck('name')->contains('pengumpul_data'));

        // Validator candidates from entries
        $validatorCandidatesFromEntries = collect();
        if (!empty($validatorIds)) {
            $validatorCandidatesFromEntries = User::with('roles', 'unitKerjas')
                ->whereIn('id', $validatorIds)
                ->get()
                ->sortByDesc(fn($u) => $u->roles->pluck('name')->contains('validator_pic') ? 1 : 0);
        }

        // Validator role candidates in unit
        $validatorRoleCandidates = $unitUsers->filter(fn($u) => $u->roles->pluck('name')->intersect(['validator_pic', 'validator'])->isNotEmpty());

        // ===== select pengumpul =====
        // Rules: prefer submitters who have pengumpul_data role and are NOT validator_pic;
        // exclude any validator_pic from being selected as pengumpul.
        $topPengumpul = null;

        if ($submitterCandidates->isNotEmpty()) {
            $preferred = $submitterCandidates->filter(
                fn($u) =>
                $u->roles->pluck('name')->contains('pengumpul_data')
                    && !$u->roles->pluck('name')->contains('validator_pic')
            );

            if ($preferred->isNotEmpty()) {
                $pool = $preferred;
            } else {
                // Exclude validator_pic submitters — these MUST NOT be pengumpul.
                $nonValidatorPicSubmitters = $submitterCandidates->reject(
                    fn($u) =>
                    $u->roles->pluck('name')->contains('validator_pic')
                );

                // If no suitable submitter remains, FALL BACK to unit users with pengumpul_data role.
                // This ensures we don't return a validator_pic just because they submitted.
                $pool = $nonValidatorPicSubmitters->isNotEmpty()
                    ? $nonValidatorPicSubmitters
                    : $pengumpulRoleCandidates->reject(fn($u) => $u->roles->pluck('name')->contains('validator_pic'));
            }

            if ($pool->isNotEmpty()) {
                $topPengumpul = $pool->sortByDesc(fn($u) => $submissionCounts[$u->id] ?? 0)->first();
            }
        } elseif ($pengumpulRoleCandidates->isNotEmpty()) {
            $candidates = $pengumpulRoleCandidates->reject(fn($u) => $u->roles->pluck('name')->contains('validator_pic'));
            if ($candidates->isNotEmpty()) {
                $topPengumpul = $candidates->sortByDesc(fn($u) => $submissionCounts[$u->id] ?? 0)->first();
            }
        }

        // ===== select validator =====
        $topValidator = null;
        if ($validatorCandidatesFromEntries->isNotEmpty()) {
            $preferredPic = $validatorCandidatesFromEntries->filter(fn($u) => $u->roles->pluck('name')->contains('validator_pic'));
            $pool = $preferredPic->isNotEmpty() ? $preferredPic : $validatorCandidatesFromEntries;
            $topValidator = $pool->sortByDesc(fn($u) => $validationCounts[$u->id] ?? 0)->first();
        } elseif ($validatorRoleCandidates->isNotEmpty()) {
            $preferredPic = $validatorRoleCandidates->filter(fn($u) => $u->roles->pluck('name')->contains('validator_pic'));
            $pool = $preferredPic->isNotEmpty() ? $preferredPic : $validatorRoleCandidates;
            $topValidator = $pool->first();
        }

        return [
            'pengumpul' => $topPengumpul,
            'validator' => $topValidator,
            'unit_users' => $unitUsers,
        ];
    }
}
