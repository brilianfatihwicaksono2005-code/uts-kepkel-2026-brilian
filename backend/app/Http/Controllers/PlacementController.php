<?php

namespace App\Http\Controllers;

use App\Models\Placement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlacementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $placements = Placement::with(['user', 'housingUnit'])->latest()->get();

        return response()->json([
            'message' => 'Daftar penempatan hunian.',
            'data' => $placements,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'housing_unit_id' => ['required', 'exists:housing_units,id'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['nullable', 'date', 'after_or_equal:check_in_date'],
        ]);

        $placement = Placement::create($validated);

        return response()->json([
            'message' => 'Penempatan hunian berhasil dibuat.',
            'data' => $placement->load(['user', 'housingUnit']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $placement = Placement::with(['user', 'housingUnit'])->find($id);

        if (! $placement) {
            return response()->json([
                'message' => 'Penempatan hunian tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail penempatan hunian.',
            'data' => $placement,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $placement = Placement::find($id);

        if (! $placement) {
            return response()->json([
                'message' => 'Penempatan hunian tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'user_id' => ['sometimes', 'required', 'exists:users,id'],
            'housing_unit_id' => ['sometimes', 'required', 'exists:housing_units,id'],
            'check_in_date' => ['sometimes', 'required', 'date'],
            'check_out_date' => ['nullable', 'date', 'after_or_equal:check_in_date'],
        ]);

        $placement->update($validated);

        return response()->json([
            'message' => 'Penempatan hunian berhasil diperbarui.',
            'data' => $placement->load(['user', 'housingUnit']),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $placement = Placement::find($id);

        if (! $placement) {
            return response()->json([
                'message' => 'Penempatan hunian tidak ditemukan.',
            ], 404);
        }

        $placement->delete();

        return response()->json([
            'message' => 'Penempatan hunian berhasil dihapus.',
        ], 200);
    }
}
