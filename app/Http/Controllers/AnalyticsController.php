<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function getAnalytics()
    {
        // I could certainly right more stuff but this will suffice for now.
        // I can extract peak hours etc.

        $totalPatients = PatientProfile::count();
        $totalDoctors = DoctorProfile::count();
        $totalPatientToDoctorRatio = $totalPatients / $totalDoctors;

        $newPatientsThisMonth = PatientProfile::whereMonth('created_at', date('m'))->count();
        $newDoctorsThisMonth = DoctorProfile::whereMonth('created_at', date('m'))->count();

        $totalAppointments = Appointment::count();
        $appointmentsThisMonth = Appointment::whereMonth('created_at', date('m'))->count();


        return response()->json([
            'total_patients' => $totalPatients,
            'total_doctors' => $totalDoctors,
            'total_patient_to_doctor_ratio' => $totalPatientToDoctorRatio,
            'new_patients_this_month' => $newPatientsThisMonth,
            'new_doctors_this_month' => $newDoctorsThisMonth,

            'total_appointments' => $totalAppointments,
            'appointments_this_month' => $appointmentsThisMonth,
        ]);
    }
}
