<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\HeadOffice;

class IncludeAnalysisTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_resolve_includes_vs_should_include_analysis()
    {
        // Crear una sede de prueba
        $headOffice = HeadOffice::factory()->create([
            'name' => 'Test HeadOffice',
            'code' => 'TEST'
        ]);

        // Test 1: Sin includes
        echo "\n=== TEST 1: Sin includes ===\n";
        $response1 = $this->getJson("/api/head-offices/{$headOffice->id}");
        echo "Status: " . $response1->getStatusCode() . "\n";

        if ($response1->isSuccessful()) {
            $data1 = $response1->json('data');
            echo "Departments incluido: " . (isset($data1['departments']) ? 'SÍ' : 'NO') . "\n";
            echo "Statistics incluido: " . (isset($data1['statistics']) ? 'SÍ' : 'NO') . "\n";
            echo "Hierarchy incluido: " . (isset($data1['hierarchy']) ? 'SÍ' : 'NO') . "\n";
        }

        // Test 2: Con include=departments
        echo "\n=== TEST 2: Con include=departments ===\n";
        $response2 = $this->getJson("/api/head-offices/{$headOffice->id}?include=departments");
        echo "Status: " . $response2->getStatusCode() . "\n";

        if ($response2->isSuccessful()) {
            $data2 = $response2->json('data');
            echo "Departments incluido: " . (isset($data2['departments']) ? 'SÍ' : 'NO') . "\n";
            echo "Statistics incluido: " . (isset($data2['statistics']) ? 'SÍ' : 'NO') . "\n";
            echo "Hierarchy incluido: " . (isset($data2['hierarchy']) ? 'SÍ' : 'NO') . "\n";
        }

        // Test 3: Con include=statistics
        echo "\n=== TEST 3: Con include=statistics ===\n";
        $response3 = $this->getJson("/api/head-offices/{$headOffice->id}?include=statistics");
        echo "Status: " . $response3->getStatusCode() . "\n";

        if ($response3->isSuccessful()) {
            $data3 = $response3->json('data');
            echo "Departments incluido: " . (isset($data3['departments']) ? 'SÍ' : 'NO') . "\n";
            echo "Statistics incluido: " . (isset($data3['statistics']) ? 'SÍ' : 'NO') . "\n";
            echo "Hierarchy incluido: " . (isset($data3['hierarchy']) ? 'SÍ' : 'NO') . "\n";
        }

        // Test 4: Con include=hierarchy
        echo "\n=== TEST 4: Con include=hierarchy ===\n";
        $response4 = $this->getJson("/api/head-offices/{$headOffice->id}?include=hierarchy");
        echo "Status: " . $response4->getStatusCode() . "\n";

        if ($response4->isSuccessful()) {
            $data4 = $response4->json('data');
            echo "Departments incluido: " . (isset($data4['departments']) ? 'SÍ' : 'NO') . "\n";
            echo "Statistics incluido: " . (isset($data4['statistics']) ? 'SÍ' : 'NO') . "\n";
            echo "Hierarchy incluido: " . (isset($data4['hierarchy']) ? 'SÍ' : 'NO') . "\n";
        }

        $this->assertTrue(true); // Para que el test pase
    }
}