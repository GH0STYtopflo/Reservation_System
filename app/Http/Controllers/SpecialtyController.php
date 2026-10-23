<?php

namespace App\Http\Controllers;

use App\Http\Requests\Specialty\StoreSpecialtyRequest;
use App\Http\Requests\Specialty\UpdateSpecialtyRequest;
use App\Http\Resources\SpecialtyResource;
use App\Models\Specialty;

class SpecialtyController extends Controller
{
    public function index()
    {
        return SpecialtyResource::collection(Specialty::all());
    }

    public function store(StoreSpecialtyRequest $request)
    {
        $data = $request->validated();

        $specialty = Specialty::create($data);

        return $specialty->toResource();
    }

    public function show(Specialty $specialty)
    {
        return $specialty->toResource();
    }

    public function update(UpdateSpecialtyRequest $request, Specialty $specialty)
    {
        $data = $request->validated();

        $specialty->update([
            'title' => $data['title'] ?? $specialty->title,
            'description' => $data['description'] ?? $specialty->description,
        ]);

        return $specialty->toResource();
    }

    public function destroy(Specialty $specialty)
    {
        $specialty->delete();

        return response(null, 204);
    }
}
