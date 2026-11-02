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
        $cities = collect([
            'Tabriz',
            'Tehran',
            'Kish',
            'Zanjan',
            'Isfahan',
            'Shiraz',
            'Mashhad',
        ])->mapWithKeys(fn (string $name) => [
            $name => City::create(['name' => $name]),
        ]);

        $specialties = collect([
            ['title' => 'Cardiology', 'description' => 'Diagnosis and treatment of cardiovascular diseases.'],
            ['title' => 'Dermatology', 'description' => 'Diagnosis and treatment of skin, hair, and nail conditions.'],
            ['title' => 'Neurology', 'description' => 'Diagnosis and treatment of disorders of the nervous system.'],
            ['title' => 'Pediatrics', 'description' => 'Medical care for infants, children, and adolescents.'],
            ['title' => 'Orthopedics', 'description' => 'Treatment of musculoskeletal conditions and injuries.'],
            ['title' => 'Ophthalmology', 'description' => 'Diagnosis and treatment of eye diseases.'],
            ['title' => 'Psychiatry', 'description' => 'Diagnosis and treatment of mental health disorders.'],
            ['title' => 'General Practice', 'description' => 'Primary healthcare and general medical services.'],
        ])->mapWithKeys(fn (array $data) => [
            $data['title'] => Specialty::create($data),
        ]);

        $password = Hash::make('StrongAsHellPass');

        // Patients
        $patients = [
            [
                'phone_number' => '00000000001',
                'first_name' => 'Ali',
                'last_name' => 'Ahmadi',
                'gender' => true,
                'blood_type' => BloodType::APlus,
                'birth_date' => '1998-05-12',
                'city' => 'Tabriz',
            ],
            [
                'phone_number' => '00000000002',
                'first_name' => 'Sara',
                'last_name' => 'Mohammadi',
                'gender' => false,
                'blood_type' => BloodType::OPlus,
                'birth_date' => '2001-09-23',
                'city' => 'Tehran',
            ],
            [
                'phone_number' => '00000000003',
                'first_name' => 'Reza',
                'last_name' => 'Karimi',
                'gender' => true,
                'blood_type' => BloodType::ABPlus,
                'birth_date' => '1985-02-17',
                'city' => 'Isfahan',
            ],
            [
                'phone_number' => '00000000004',
                'first_name' => 'Neda',
                'last_name' => 'Hosseini',
                'gender' => false,
                'blood_type' => BloodType::BNeg,
                'birth_date' => '1995-11-04',
                'city' => 'Shiraz',
            ],
            [
                'phone_number' => '00000000005',
                'first_name' => 'Mohammad',
                'last_name' => 'Rahimi',
                'gender' => true,
                'blood_type' => BloodType::ONeg,
                'birth_date' => '1979-07-30',
                'city' => 'Mashhad',
            ],
        ];

        foreach ($patients as $data) {
            $user = User::create([
                'phone_number' => $data['phone_number'],
                'role' => Role::PATIENT,
                'password' => $password,
            ]);

            $user->patientProfile()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'gender' => $data['gender'],
                'blood_type' => $data['blood_type'],
                'birth_date' => $data['birth_date'],
                'city_id' => $cities[$data['city']]->id,
            ]);
        }

        // Doctors
        $doctors = [
            [
                'phone_number' => '11111111111',
                'first_name' => 'Arman',
                'last_name' => 'Ebrahimi',
                'gender' => true,
                'education' => 'MD, Tehran University of Medical Sciences',
                'city' => 'Tehran',
                'specialties' => ['Cardiology'],
            ],
            [
                'phone_number' => '11111111112',
                'first_name' => 'Maryam',
                'last_name' => 'Safari',
                'gender' => false,
                'education' => 'MD, Tabriz University of Medical Sciences',
                'city' => 'Tabriz',
                'specialties' => ['Dermatology'],
            ],
            [
                'phone_number' => '11111111113',
                'first_name' => 'Amir',
                'last_name' => 'Kazemi',
                'gender' => true,
                'education' => 'MD, Shiraz University of Medical Sciences',
                'city' => 'Shiraz',
                'specialties' => ['Neurology', 'Psychiatry'],
            ],
            [
                'phone_number' => '11111111114',
                'first_name' => 'Fatemeh',
                'last_name' => 'Moradi',
                'gender' => false,
                'education' => 'MD, Isfahan University of Medical Sciences',
                'city' => 'Isfahan',
                'specialties' => ['Pediatrics'],
            ],
            [
                'phone_number' => '11111111115',
                'first_name' => 'Hossein',
                'last_name' => 'Jafari',
                'gender' => true,
                'education' => 'MD, Mashhad University of Medical Sciences',
                'city' => 'Mashhad',
                'specialties' => ['Orthopedics'],
            ],
            [
                'phone_number' => '11111111116',
                'first_name' => 'Elham',
                'last_name' => 'Karimi',
                'gender' => false,
                'education' => 'MD, Tehran University of Medical Sciences',
                'city' => 'Tehran',
                'specialties' => ['Ophthalmology', 'General Practice'],
            ],
        ];

        foreach ($doctors as $data) {
            $user = User::create([
                'phone_number' => $data['phone_number'],
                'role' => Role::DOCTOR,
                'password' => $password,
            ]);

            $user->doctorProfile()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'gender' => $data['gender'],
                'education' => $data['education'],
                'city_id' => $cities[$data['city']]->id,
            ])->doctorSpecialties()->attach(
                collect($data['specialties'])
                    ->map(fn (string $title) => $specialties[$title]->id)
            );
        }

        // Admin
        User::create([
            'phone_number' => '66666666666',
            'role' => Role::ADMIN,
            'password' => $password,
        ]);
    }
}
