<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Remove position_id column from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
        });

        // Drop positions table
        Schema::dropIfExists('positions');

        // Update roles to new structure
        $this->updateRoles();
    }

    public function down(): void
    {
        // Recreate positions table
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Add position_id back to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
        });
    }

    private function updateRoles(): void
    {
        // Delete Tim IT role
        Role::where('name', 'tim_it')->orWhere('name', 'IT')->delete();

        // Define the 4 required roles
        $roles = [
            [
                'name' => 'admin',
                'label' => 'Administrator',
                'guard_name' => 'web'
            ],
            [
                'name' => 'tim_mutu',
                'label' => 'Tim Mutu',
                'guard_name' => 'web'
            ],
            [
                'name' => 'pengumpul_data',
                'label' => 'Unit Kerja - Pengumpul Data',
                'guard_name' => 'web'
            ],
            [
                'name' => 'validator_pic',
                'label' => 'Unit Kerja - Validator/PIC Indikator',
                'guard_name' => 'web'
            ]
        ];

        // Update existing roles or create new ones
        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        // Clean up old role names
        Role::whereNotIn('name', ['admin', 'tim_mutu', 'pengumpul_data', 'validator_pic'])->delete();
    }
};
