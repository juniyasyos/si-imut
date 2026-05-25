<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Throwable;
use Carbon\Carbon;
use App\Models\ImutData;
use App\Models\LaporanImut;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImutIndicatorReportController extends Controller
{
    public function index()
    {
        // Redirect to a default or list page, or show error
        abort(404, 'Please specify an indicator and period.');
    }

    public function show(Request $request, $indicator, $periode)
    {
        // Find the ImutData by slug or id
        $imutData = ImutData::where('slug', $indicator)->orWhere('id', $indicator)->firstOrFail();

        // Find the LaporanImut
        $laporan = LaporanImut::findOrFail($periode);

        // Find a user in role 'Tim Mutu' to use as the left-side signer (if any).
        // Guard against missing role to avoid RoleDoesNotExist exceptions.
        $timMutuUser = null;
        try {
            if (class_exists(Role::class) && Role::where('name', 'Tim Mutu')->exists()) {
                $timMutuUser = User::role('Tim Mutu')->orderBy('name')->first();
            }
        } catch (Throwable $e) {
            // swallow - treat as not found
            $timMutuUser = null;
        }

        // Try to locate user 'Suharnik' (search name/email/username) — controller provides the final values
        $penanggungJawabUser = User::whereRaw('LOWER(name) LIKE ?', ['%suharnanik%'])
            ->orWhere('email', 'like', '%suharnik%')
            ->orWhere('nip', 'like', '%suharnik%')
            ->first();

        // Resolve signer display values here (keep Blade simple)
        $leftSignerUser = $timMutuUser ?? $laporan->createdBy ?? null;
        $leftSignerName = $leftSignerUser?->name ?? ($laporan->created_by ? (User::find($laporan->created_by)?->name ?? null) : null) ?? '(...........................)';
        $leftSignerImage = $leftSignerUser?->getFilamentTtdUrl() ?? null;

        $rightSignerName = $penanggungJawabUser?->name;
        $rightSignerImage = $penanggungJawabUser?->getFilamentTtdUrl() ?? null;

        // Debug info (pass to view so Blade remains clean)
        $safeExists = function (?string $path) {
            if (! $path) {
                return false;
            }
            try {
                if (preg_match('#^https?://#i', $path) || str_starts_with($path, '/')) {
                    // if absolute or root-relative we cannot reliably check via Storage; assume true
                    return true;
                }
                if (Storage::disk('public')->exists(ltrim($path, '/'))) {
                    return true;
                }
                if (config('filesystems.disks.s3') && Storage::disk('s3')->exists(ltrim($path, '/'))) {
                    return true;
                }
            } catch (Throwable $e) {
                return false;
            }
            return false;
        };

        // $ttdDebug = [
        //     'timMutuUser' => $timMutuUser ? ['id' => $timMutuUser->id, 'name' => $timMutuUser->name, 'ttd_url' => $timMutuUser->ttd_url, 'getFilamentTtdUrl' => $timMutuUser->getFilamentTtdUrl()] : null,
        //     'laporanCreatedBy' => $laporan->createdBy ? ['id' => $laporan->createdBy->id, 'name' => $laporan->createdBy->name, 'ttd_url' => $laporan->createdBy->ttd_url, 'getFilamentTtdUrl' => $laporan->createdBy->getFilamentTtdUrl()] : null,
        //     'penanggungJawabUser' => $penanggungJawabUser ? ['id' => $penanggungJawabUser->id, 'name' => $penanggungJawabUser->name, 'ttd_url' => $penanggungJawabUser->ttd_url, 'getFilamentTtdUrl' => $penanggungJawabUser->getFilamentTtdUrl()] : null,
        //     'resolvedLeftSignerImage' => $leftSignerImage,
        //     'resolvedRightSignerImage' => $rightSignerImage,
        //     'leftSignerFileExistsPublic' => $leftSignerUser?->ttd_url ? $safeExists($leftSignerUser->ttd_url) : null,
        //     'creatorFileExistsPublic' => $laporan->createdBy?->ttd_url ? $safeExists($laporan->createdBy->ttd_url) : null,
        //     'pjFileExistsPublic' => $penanggungJawabUser?->ttd_url ? $safeExists($penanggungJawabUser->ttd_url) : null,
        // ];

        // Signature date (prefer laporan.created_at, fallback to report month/year or today)
        $signatureDate = $laporan->created_at?->translatedFormat('j F Y')
            ?? (($laporan->report_month && $laporan->report_year) ? Carbon::create($laporan->report_year, $laporan->report_month, 1)->translatedFormat('j F Y') : now()->translatedFormat('j F Y'));

        // Return the HTML view with Alpine.js — pass only ready-to-render variables
        return view('reports.imut-indicator-report')->with([
            'imutData' => $imutData,
            'laporan' => $laporan,
            'timMutuUser' => $timMutuUser,
            'penanggungJawabUser' => $penanggungJawabUser,
            'leftSignerName' => $leftSignerName,
            'leftSignerImage' => $leftSignerImage,
            'rightSignerName' => $rightSignerName,
            'rightSignerImage' => $rightSignerImage,
            'signatureDate' => $signatureDate,
            // 'ttdDebug' => $ttdDebug,
        ]);
    }

    public function detail(Request $request, $indicator, $periode, $filter_periode = null, $catatan = null)
    {
        // Similar to show, but with additional parameters
        return $this->show($request, $indicator, $periode);
    }
}
