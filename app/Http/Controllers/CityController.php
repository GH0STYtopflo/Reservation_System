<?php

namespace App\Http\Controllers;

use App\Http\Requests\City\StoreCityRequest;
use App\Http\Resources\CityResource;
use App\Models\City;
use Gate;

class CityController extends Controller
{
    public function index()
    {
        return CityResource::collection(City::all());
    }

    public function store(StoreCityRequest $request)
    {
        $data = $request->validated();

        $city = City::create($data);

        return $city->toResource()->response()->setStatusCode(201);
    }

    public function show(City $city)
    {
        return $city->toResource();
    }

    public function update(StoreCityRequest $request, City $city)
    {
        $data = $request->validated();

        $city->update($data);

        return $city->toResource();
    }

    public function destroy(City $city)
    {
        Gate::authorize('delete', $city);

        $city->delete();

        return response(null, 204);
    }
}
