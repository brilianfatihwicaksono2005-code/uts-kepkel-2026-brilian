<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MaintenanceTicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $tickets = MaintenanceTicket::with(['housingUnit', 'user'])->latest()->get();

        return response()->json([
            'message' => 'Daftar tiket maintenance.',
            'data' => $tickets,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'housing_unit_id' => ['required', 'exists:housing_units,id'],
            'description' => ['required', 'string'],
            'urgency' => ['required', Rule::in(['low', 'medium', 'high'])],
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved'])],
        ]);

        $validated['user_id'] = Auth::id();

        $ticket = MaintenanceTicket::create($validated);

        return response()->json([
            'message' => 'Tiket maintenance berhasil dibuat.',
            'data' => $ticket->load(['housingUnit', 'user']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $ticket = MaintenanceTicket::with(['housingUnit', 'user'])->find($id);

        if (! $ticket) {
            return response()->json([
                'message' => 'Tiket maintenance tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail tiket maintenance.',
            'data' => $ticket,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $ticket = MaintenanceTicket::find($id);

        if (! $ticket) {
            return response()->json([
                'message' => 'Tiket maintenance tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'housing_unit_id' => ['sometimes', 'required', 'exists:housing_units,id'],
            'description' => ['sometimes', 'required', 'string'],
            'urgency' => ['sometimes', 'required', Rule::in(['low', 'medium', 'high'])],
            'status' => ['sometimes', 'required', Rule::in(['open', 'in_progress', 'resolved'])],
        ]);

        $ticket->update($validated);

        return response()->json([
            'message' => 'Tiket maintenance berhasil diperbarui.',
            'data' => $ticket->load(['housingUnit', 'user']),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $ticket = MaintenanceTicket::find($id);

        if (! $ticket) {
            return response()->json([
                'message' => 'Tiket maintenance tidak ditemukan.',
            ], 404);
        }

        $ticket->delete();

        return response()->json([
            'message' => 'Tiket maintenance berhasil dihapus.',
        ], 200);
    }
}
