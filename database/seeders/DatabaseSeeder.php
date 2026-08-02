<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $faker = Faker::create('id_ID');
        $now = Carbon::now();
        $password = Hash::make('password123');
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $rolePenilai = Role::firstOrCreate(['name' => 'penilai']);
        $rolePemohon = Role::firstOrCreate(['name' => 'pemohon']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Administrator', 'password' => $password]
        );
        $admin->assignRole($roleAdmin);

        $this->command->info('Memulai Bulk Insert 1000 Pemohon & 1000 Penilai...');

        $pemohonData = [];
        $penilaiData = [];
        
        for ($i = 1; $i <= 1000; $i++) {
            $pemohonData[] = [
                'name' => $faker->name,
                'email' => "pemohon{$i}@test.com",
                'password' => $password,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            
            $penilaiData[] = [
                'name' => $faker->name,
                'email' => "penilai{$i}@test.com",
                'password' => $password,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        User::insert($pemohonData);
        User::insert($penilaiData);
        
        $pemohonIds = User::where('email', 'like', 'pemohon%')->pluck('id')->toArray();
        $penilaiIds = User::where('email', 'like', 'penilai%')->pluck('id')->toArray();

        $pivotData = [];
        foreach ($pemohonIds as $id) {
            $pivotData[] = ['role_id' => $rolePemohon->id, 'model_type' => User::class, 'model_id' => $id];
        }
        foreach ($penilaiIds as $id) {
            $pivotData[] = ['role_id' => $rolePenilai->id, 'model_type' => User::class, 'model_id' => $id];
        }

        DB::table('model_has_roles')->insert($pivotData);

        $this->command->info('Memulai Bulk Insert 10.000 Permohonan Dokumen (Dicicil per 2000)...');
        $statuses = ['draft', 'submitted', 'revisi', 'approved', 'rejected'];
        $permohonanChunk = [];
        
        for ($i = 1; $i <= 10000; $i++) {
            $randomDate = $faker->dateTimeBetween('-1 year', 'now');
            $nomorPermohonan = 'PRM-' . $randomDate->format('Ymd') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT);

            $permohonanChunk[] = [
                'nomor_permohonan' => $nomorPermohonan,
                'pemohon_id'       => $faker->randomElement($pemohonIds),
                'judul_project'    => "permohonan ke-{$i}",
                'deskripsi'        => "deskripsi permohonan ke-{$i}",
                'status'           => $faker->randomElement($statuses),
                'created_at'       => $randomDate,
                'updated_at'       => $randomDate,
            ];

            if ($i % 2000 == 0) {
                DB::table('permohonans')->insert($permohonanChunk);
                $permohonanChunk = []; 
                $this->command->info("Berhasil insert {$i} permohonan...");
            }
        }
        
        $this->command->info('Proses Seeding 12.000+ Data Berhasil Diselesaikan dengan Cepat!');
    }
}