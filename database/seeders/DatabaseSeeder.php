<?php

namespace Database\Seeders;

use App\Enums\BloodType;
use App\Enums\Role;
use App\Models\City;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $cities = collect(['Tabriz', 'Tehran', 'Kish', 'Zanjan'])
            ->mapWithKeys(fn (string $name) => [
                $name => City::create(['name' => $name]),
            ]);

        foreach (['A', 'B', 'C'] as $title) {
            Specialty::create([
                'title' => $title,
                'description' => 'nothing',
            ]);
        }

        $patient = User::create([
            'phone_number' => '00000000000',
            'role' => Role::PATIENT,
            'password' => Hash::make('StrongAsHellPass'),
        ]);

        $patient->patientProfile()->create([
            'first_name' => 'name',
            'last_name' => 'name',
            'gender' => false,
            'blood_type' => BloodType::ABPlus,
            'birth_date' => now(),
            'city_id' => $cities['Tabriz']->id,
        ]);

        $doctor = User::create([
            'phone_number' => '11111111111',
            'role' => Role::DOCTOR,
            'password' => Hash::make('StrongAsHellPass'),
        ]);

        $doctor->doctorProfile()->create([
            'first_name' => 'name',
            'last_name' => 'name',
            'gender' => false,
            'education' => 'idk',
            'city_id' => $cities['Tehran']->id,
        ]);
    }
}
