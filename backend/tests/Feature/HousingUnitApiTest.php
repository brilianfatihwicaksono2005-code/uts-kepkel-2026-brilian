<?php

namespace Tests\Feature;

use App\Models\HousingUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HousingUnitApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dapat_mengambil_daftar_unit(): void
    {
        Sanctum::actingAs(User::factory()->create());

        HousingUnit::create([
            'unit_number' => 'A-101',
            'capacity' => 2,
            'status' => 'available',
        ]);

        $response = $this->getJson('/api/housing-units');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_gagal_membuat_unit_jika_body_kosong(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/housing-units', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['unit_number', 'capacity', 'status']);
    }

    public function test_berhasil_membuat_unit_jika_data_valid(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $payload = [
            'unit_number' => 'B-202',
            'capacity' => 4,
            'status' => 'available',
        ];

        $response = $this->postJson('/api/housing-units', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.unit_number', 'B-202');

        $this->assertDatabaseHas('housing_units', $payload);
    }

    public function test_gagal_mengambil_unit_yang_tidak_ada(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/housing-units/999999');

        $response->assertStatus(404);
    }

    public function test_gagal_mengakses_endpoint_tanpa_token(): void
    {
        $response = $this->getJson('/api/housing-units');

        $response->assertStatus(401);
    }
}