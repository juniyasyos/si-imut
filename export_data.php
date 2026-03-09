<?php

require_once __DIR__ . '/bootstrap/app.php';

use App\Models\User;
use App\Models\UnitKerja;
use Illuminate\Support\Facades\DB;

// Get the application instance
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Export Users
$users = User::query()
    ->selectRaw('
        id,
        nip as id_alt,
        name as nama,
        place_of_birth as tempat_lahir,
        date_of_birth as tanggal_lahir,
        gender as jenis_kelamin,
        address_ktp as alamat,
        phone_number as nomor_telepon,
        email,
        status,
        avatar_url,
        ttd_url
    ')
    ->get()
    ->map(function ($user) {
        return [
            'id' => $user->id,
            'nip' => $user->id_alt,
            'nama' => $user->nama,
            'tempat_lahir' => $user->tempat_lahir,
            'tanggal_lahir' => $user->tanggal_lahir,
            'jenis_kelamin' => $user->jenis_kelamin,
            'alamat' => $user->alamat,
            'nomor_telepon' => $user->nomor_telepon,
            'email' => $user->email,
            'status' => $user->status,
            'avatar_url' => $user->avatar_url,
            'ttd_url' => $user->ttd_url,
        ];
    })
    ->toArray();

// Export Unit Kerja
$unitKerja = UnitKerja::query()
    ->selectRaw('
        id,
        unit_name as nama_unit,
        slug,
        description as deskripsi
    ')
    ->get()
    ->map(function ($unit) {
        return [
            'id' => $unit->id,
            'nama_unit' => $unit->nama_unit,
            'slug' => $unit->slug,
            'deskripsi' => $unit->deskripsi,
        ];
    })
    ->toArray();

// Export User-Unit Kerja Relationships
$userUnitKerja = DB::table('user_unit_kerja')
    ->join('users', 'user_unit_kerja.user_id', '=', 'users.id')
    ->join('unit_kerja', 'user_unit_kerja.unit_kerja_id', '=', 'unit_kerja.id')
    ->select(
        'users.id as user_id',
        'users.nip',
        'users.name',
        'unit_kerja.id as unit_kerja_id',
        'unit_kerja.unit_name',
        'user_unit_kerja.created_at'
    )
    ->get()
    ->map(function ($row) {
        return [
            'user_id' => $row->user_id,
            'user_nip' => $row->nip,
            'user_name' => $row->name,
            'unit_kerja_id' => $row->unit_kerja_id,
            'unit_kerja_name' => $row->unit_name,
            'assigned_at' => $row->created_at,
        ];
    })
    ->toArray();

// Create data directory if it doesn't exist
$dataDir = __DIR__ . '/database/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// Save to JSON files
file_put_contents(
    $dataDir . '/users.json',
    json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

file_put_contents(
    $dataDir . '/unit_kerja.json',
    json_encode($unitKerja, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

file_put_contents(
    $dataDir . '/user_unit_kerja.json',
    json_encode($userUnitKerja, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "✓ Data exported successfully!\n";
echo "  - Users saved to: database/data/users.json\n";
echo "  - Unit Kerja saved to: database/data/unit_kerja.json\n";
echo "  - User-Unit Kerja mapping saved to: database/data/user_unit_kerja.json\n";
echo "\nTotal records:\n";
echo "  - Users: " . count($users) . "\n";
echo "  - Unit Kerja: " . count($unitKerja) . "\n";
echo "  - User-Unit Kerja mappings: " . count($userUnitKerja) . "\n";
