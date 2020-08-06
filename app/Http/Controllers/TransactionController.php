<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    protected $transactionService;
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $request->validate([
            'payer' => 'required|exists:mysql.persons,id',
            'payee' => 'required|exists:mysql.persons,id',
            'value' => 'required|integer|min:0',
        ]);
        
        $this->transactionService->handle($request);
        $data = [
            'message' => 'Transação realizada com sucesso'
        ];
        return response()->json($data, 201);
    }
}
