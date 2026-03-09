<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UnitKerja;
use Spatie\Permission\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportDataToJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:export-json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export users, roles, unit kerja data and relationships to JSON files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Export Users
        $users = User::query()
            ->where('deleted_at', null)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nip' => $user->nip,
                    'nama' => $user->name,
                    'tempat_lahir' => $user->place_of_birth,
                    'tanggal_lahir' => $user->date_of_birth,
                    'jenis_kelamin' => $user->gender,
                    'alamat' => $user->address_ktp,
                    'nomor_telepon' => $user->phone_number,
                    'email' => $user->email,
                    'status' => $user->status,
                    'avatar_url' => $user->avatar_url,
                    'ttd_url' => $user->ttd_url,
                ];
            })
            ->toArray();

        // Export Roles
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'label' => $role->label,
                    'guard_name' => $role->guard_name,
                ];
            })
            ->toArray();

        // Export Unit Kerja
        $unitKerja = UnitKerja::query()
            ->where('deleted_at', null)
            ->get()
            ->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'nama_unit' => $unit->unit_name,
                    'slug' => $unit->slug,
                    'deskripsi' => $unit->description,
                ];
            })
            ->toArray();

        // Export User-Unit Kerja Relationships
        $userUnitKerja = DB::table('user_unit_kerja')
            ->join('users', 'user_unit_kerja.user_id', '=', 'users.id')
            ->join('unit_kerja', 'user_unit_kerja.unit_kerja_id', '=', 'unit_kerja.id')
            ->where('users.deleted_at', null)
            ->where('unit_kerja.deleted_at', null)
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

        // Export User-Role Relationships
        $userRoles = DB::table('model_has_roles')
            ->join('users', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_type', User::class)
            ->where('roles.guard_name', 'web')
            ->where('users.deleted_at', null)
            ->select(
                'users.id as user_id',
                'users.nip',
                'users.name',
                'roles.id as role_id',
                'roles.name',
                'roles.label'
            )
            ->get()
            ->map(function ($row) {
                return [
                    'user_id' => $row->user_id,
                    'user_nip' => $row->nip,
                    'user_name' => $row->name,
                    'role_id' => $row->role_id,
                    'role_name' => $row->name,
                    'role_label' => $row->label,
                ];
            })
            ->toArray();

        // Create data directory if it doesn't exist
        $dataDir = database_path('data');
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        // Save to JSON files
        file_put_contents(
            $dataDir . '/users.json',
            json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        file_put_contents(
            $dataDir . '/roles.json',
            json_encode($roles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        file_put_contents(
            $dataDir . '/unit_kerja.json',
            json_encode($unitKerja, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        file_put_contents(
            $dataDir . '/user_unit_kerja.json',
            json_encode($userUnitKerja, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        file_put_contents(
            $dataDir . '/user_roles.json',
            json_encode($userRoles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->info('✓ Data exported successfully!');
        $this->info('  - Users saved to: database/data/users.json');
        $this->info('  - Roles saved to: database/data/roles.json');
        $this->info('  - Unit Kerja saved to: database/data/unit_kerja.json');
        $this->info('  - User-Unit Kerja mapping saved to: database/data/user_unit_kerja.json');
        $this->info('  - User-Role mapping saved to: database/data/user_roles.json');
        $this->newLine();
        $this->info('Total records:');
        $this->info('  - Users: ' . count($users));
        $this->info('  - Roles: ' . count($roles));
        $this->info('  - Unit Kerja: ' . count($unitKerja));
        $this->info('  - User-Unit Kerja mappings: ' . count($userUnitKerja));
        $this->info('  - User-Role mappings: ' . count($userRoles));
    }
}
