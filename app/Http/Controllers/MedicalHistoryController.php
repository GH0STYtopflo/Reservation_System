<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalHistory\StoreHistoryRequest;
use App\Http\Requests\MedicalHistory\UpdateHistoryRequest;
use App\Models\MedicalHistory;
use App\Models\PatientProfile;
use Gate;
use Illuminate\Support\Facades\Response;

class MedicalHistoryController extends Controller
{
    public function index(PatientProfile $patientProfile)
    {
        Gate::authorize('vc', [MedicalHistory::class, $patientProfile]);

        return $patientProfile->medicalHistories->toResourceCollection();
    }

    public function store(StoreHistoryRequest $request, PatientProfile $patientProfile)
    {
        Gate::authorize('vc', [MedicalHistory::class, $patientProfile]);

        $patientProfile->medicalHistories()->create($request->validated());

        return $patientProfile->refresh()->toResource();
    }

    public function show($patientProfile, MedicalHistory $medicalHistory)
    {
        Gate::authorize('vud', $medicalHistory);

        return $medicalHistory->toResource();
    }

    public function update(UpdateHistoryRequest $request, $patientProfile, MedicalHistory $medicalHistory)
    {
        Gate::authorize('vud', $medicalHistory);

        $data = $request->validated();

        $medicalHistory->update([
            'title' => $data['title'] ?? $medicalHistory->title,
            'description' => $data['description'] ?? $medicalHistory->description,
            'type' => $data['type'] ?? $medicalHistory->type,
        ]);

        return $medicalHistory->refresh()->toResource();
    }

    public function destroy($patientProfile, MedicalHistory $medicalHistory)
    {
        Gate::authorize('vud', $medicalHistory);

        $medicalHistory->delete();

        return Response::noContent();
    }
}
