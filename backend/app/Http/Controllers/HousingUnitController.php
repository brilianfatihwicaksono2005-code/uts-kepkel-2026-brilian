<?php

namespace App\Http\Controllers;

use App\Models\HousingUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HousingUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $housingUnits = HousingUnit::latest()->get();

        return response()->json([
            'message' => 'Daftar unit hunian.',
            'data' => $housingUnits,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unit_number' => ['required', 'string', 'max:255', 'unique:housing_units,unit_number'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(['available', 'occupied', 'maintenance'])],
        ]);

        $housingUnit = HousingUnit::create($validated);

        return response()->json([
            'message' => 'Unit hunian berhasil dibuat.',
            'data' => $housingUnit,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $housingUnit = HousingUnit::find($id);

        if (! $housingUnit) {
            return response()->json([
                'message' => 'Unit hunian tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail unit hunian.',
            'data' => $housingUnit,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $housingUnit = HousingUnit::find($id);

        if (! $housingUnit) {
            return response()->json([
                'message' => 'Unit hunian tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'unit_number' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('housing_units', 'unit_number')->ignore($housingUnit->id)],
            'capacity' => ['sometimes', 'required', 'integer', 'min:1'],
            'status' => ['sometimes', 'required', Rule::in(['available', 'occupied', 'maintenance'])],
        ]);

        $housingUnit->update($validated);

        return response()->json([
            'message' => 'Unit hunian berhasil diperbarui.',
            'data' => $housingUnit,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $housingUnit = HousingUnit::find($id);

        if (! $housingUnit) {
            return response()->json([
                'message' => 'Unit hunian tidak ditemukan.',
            ], 404);
        }

        $housingUnit->delete();

        return response()->json([
            'message' => 'Unit hunian berhasil dihapus.',
        ], 200);
    }
}