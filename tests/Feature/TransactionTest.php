<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    /** @test */
    public function check_if_transaction_is_correct()
    {
        $response = $this->json(
            'POST',
            '/transaction',
            [
                'value' => 100.00,
                'payer'=> 2,
                'payee' => 1
            ]
        );

        $response
            ->assertStatus(200);
    }
    
    /** @test */
    public function check_if_transaction_value_is_negative()
    {
        $response = $this->json(
            'POST',
            '/transaction',
            [
                'value' => -100.00,
                'payer'=> 2,
                'payee' => 1
            ]
        );

        $response
            ->assertStatus(400);
    }
    
    /** @test */
    public function check_if_transaction_user_not_exists()
    {
        $response = $this->json(
            'POST',
            '/transaction',
            [
                'value' => 100.00,
                'payer'=> 3,
                'payee' => 1
            ]
        );

        $response
            ->assertStatus(400);
    }
    
    /** @test */
    public function check_if_transaction_values_is_not_expected()
    {
        $response = $this->json(
            'POST',
            '/transaction',
            [
                'value' => "100.00",
                'payer'=> "3",
                'payee' => "1"
            ]
        );

        $response
            ->assertStatus(500);
    }
}
