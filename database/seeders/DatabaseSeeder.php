<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Role Spatie
        $rolePemohon = Role::firstOrCreate(['name' => 'pemohon']);
        $rolePenilai = Role::firstOrCreate(['name' => 'penilai']);

        // 2. Buat Akun Dummy Utama untuk Login Testing
        $demoPemohon = User::create([
            'name' => 'Pemohon Demo',
            'email' => 'pemohon@test.com',
            'password' => Hash::make('password123'),
        ]);
        $demoPemohon->assignRole($rolePemohon);

        $demoPenilai = User::create([
            'name' => 'Penilai Demo',
            'email' => 'penilai@test.com',
            'password' => Hash::make('password123'),
        ]);
        $demoPenilai->assignRole($rolePenilai);

        // 3. Generate 1.000 Pemohon & 1.000 Penilai (Bulk Insert)
        $now = now();
        $password = Hash::make('password');

        $pemohons = [];
        for ($i = 1; $i <= 999; $i++) {
            $pemohons[] = [
                'name' => "Pemohon $i",
                'email' => "pemohon$i@test.com",
                'password' => $password,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('users')->insert($pemohons);

        $penilais = [];
        for ($i = 1; $i <= 999; $i++) {
            $penilais[] = [
                'name' => "Penilai $i",
                'email' => "penilai$i@test.com",
                'password' => $password,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('users')->insert($penilais);

        // Assign Spatie Roles ke user yang di-insert
        User::where('email', 'LIKE', 'pemohon%')->get()->each(fn($u) => $u->assignRole($rolePemohon));
        User::where('email', 'LIKE', 'penilai%')->get()->each(fn($u) => $u->assignRole($rolePenilai));

        // 4. Generate 10.000 Data Permohonan (Chunk Insert)
        $pemohonIds = User::role('pemohon')->pluck('id')->toArray();
        $statuses = ['draft', 'submitted', 'revisi', 'approved', 'rejected'];

        $permohonanData = [];
        for ($i = 1; $i <= 10000; $i++) {
            $permohonanData[] = [
                'nomor_permohonan' => 'PRM-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'pemohon_id' => $pemohonIds[array_rand($pemohonIds)],
                'judul_project' => "Permohonan Dokumen Kelayakan #$i",
                'deskripsi' => "Deskripsi permohonan dokumen kelayakan ke-$i",
                'status' => $statuses[array_rand($statuses)],
                'created_at' => now()->subDays(rand(1, 90)),
                'updated_at' => $now,
            ];

            // Direct Insert per 1000 data agar tidak out of memory
            if ($i % 1000 === 0) {
                DB::table('permohonans')->insert($permohonanData);
                $permohonanData = [];
            }
        }
    }
}