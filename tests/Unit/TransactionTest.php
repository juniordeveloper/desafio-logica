<?php

namespace Tests\Unit;

use App\Entities\Person;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class TransactionTest extends TestCase
{
    
    /** @test */
    public function teste_complete_handle()
    {
        $transactionService = app('App\Services\TransactionService');

        $request = new Request();
        $request->merge([
            'value' => 100.00,
            'payer'=> 2,
            'payee' => 1
        ]);
        $transactionService->handle($request);
        $this->assertTrue(true);
    }
    
    /** 
     * @test
     * */
    public function teste_payer_type_diff_support()
    {
        $transactionService = app('App\Services\TransactionService');
        $this->expectExceptionCode(400);

        $request = new Request();
        $request->merge([
            'value' => 100.00,
            'payer'=> 1,
            'payee' => 2
        ]);
        $transactionService->handle($request);
    }
}
