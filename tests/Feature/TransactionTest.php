<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Entities\Person;

class TransactionTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function check_if_transaction_is_correct()
    {
        $personPJ = factory(Person::class)->create(['type'=>'PJ']);
        $personPF = factory(Person::class)->create(['type'=>'PF']);

        $response = $this->json(
            'POST',
            '/transaction',
            [
                'value' => 100,
                'payer'=> $personPF->id,
                'payee' => $personPJ->id
            ]
        );
        $response
            ->assertStatus(201);
    }
    
    /** @test */
    public function check_if_transaction_is_person_not_valid()
    {
        $personPJ = factory(Person::class)->create(['type'=>'PJ']);
        $personPF = factory(Person::class)->create(['type'=>'PF']);

        $response = $this->json(
            'POST',
            '/transaction',
            [
                'value' => 100,
                'payer'=> $personPJ->id,
                'payee' => $personPF->id
            ]
        );
        
        $response
            ->assertStatus(400);
    }
    
    /** @test */
    public function check_if_transaction_value_is_negative()
    {
        $response = $this->json(
            'POST',
            '/transaction',
            [
                'value' => -100,
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
                'value' => 100,
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
                'value' => "100",
                'payer'=> "3",
                'payee' => "1"
            ]
        );
        $response
            ->assertStatus(400);
    }
}
