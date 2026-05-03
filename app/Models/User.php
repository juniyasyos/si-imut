<?php

namespace App\Models;

use Filament\Panel;
use App\Models\UnitKerja;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use App\Support\StorageFallback;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use App\Traits\HasUniqueWithSoftDeletes;

/**
 * Model User
 *
 * Model ini mewakili pengguna dalam sistem, dengan atribut yang menyimpan informasi pribadi,
 * kontak, keamanan, dan status. Relasi dengan `UnitKerja` juga dikelola melalui
 * model ini.
 *
 * @property int $id
 * @property string $nip
 * @property string $name
 * @property string $place_of_birth
 * @property \Carbon\Carbon $date_of_birth
 * @property string $gender
 * @property string $address_ktp
 * @property string|null $phone_number
 * @property string|null $email
 * @property string|null $password
 * @property string|null $status
 * @property \Carbon\Carbon|null $email_verified_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePlaceOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAddressKtp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 */
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, TwoFactorAuthenticatable, LogsActivity, SoftDeletes, HasUniqueWithSoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'iam_id',
        'nip',
        'active',
        'name',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'address_ktp',
        'phone_number',
        'email',
        'password',
        'status',
        'avatar_url',
        'ttd_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be appended when serializing.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'ttd_presigned_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
        ];
    }




    /**
     * Relasi ke UnitKerja dengan tabel pivot
     *
     * @return BelongsToMany
     */
    public function unitKerjas(): BelongsToMany
    {
        return $this->belongsToMany(UnitKerja::class, 'user_unit_kerja', 'user_id', 'unit_kerja_id')
            ->withTimestamps();
    }

    /**
     * Mengambil URL avatar pengguna untuk Filament
     *
     * @return string|null
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    /**
     * Mengambil URL TTD presigned dari Siimut API (jika IAM enabled) atau local storage.
     * - Shortcut ke SignatoryService::getTtdUrl()
     * - Jika IAM enabled: ambil dari IAM API (presigned URL, valid 15 menit)
     * - Jika IAM disabled: ambil dari S3 atau public disk lokal
     *
     * Usage: $user->ttd_presigned_url
     *
     * @return string|null
     */
    public function getTtdPresignedUrlAttribute(): ?string
    {
        return app(\App\Services\SignatoryService::class)->getTtdUrl($this);
    }

    /**
     * Mengambil URL TTD pengguna untuk Filament.
     * - Prioritaskan S3 jika tersedia (MinIO aktif).
     * - Jika S3 tidak tersedia, gunakan `public` disk (accessible via /storage).
     * - Method tidak mengubah nilai DB (`ttd_url` tetap path relatif).
     *
     * @return string|null
     *
     * @deprecated Use $user->ttd_presigned_url instead
     */
    public function getFilamentTtdUrl(): ?string
    {
        return $this->ttd_presigned_url;
    }

    /**
     * Memeriksa apakah pengguna dapat mengakses panel Filament.
     *
     * @param Panel $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Mendapatkan pengaturan log aktivitas untuk model ini.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    /**
     * Mutator untuk mendapatkan status pengguna dalam format yang lebih rapi.
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Get validation rules for unique fields with soft deletes
     *
     * @param int|null $ignoreId
     * @return array
     */
    public function getUniqueValidationRules(?int $ignoreId = null): array
    {
        return [
            'nip' => ['required', 'string', $this->uniqueRule('nip', $ignoreId)],
            'email' => ['nullable', 'email', $this->uniqueRule('email', $ignoreId)],
        ];
    }
}
