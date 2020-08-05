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
        $transaction = $this->transactionService->handle($request);
        $data = [
            'message' => 'Transação realizada com sucesso'
        ];
        return response()->json($data);
    }
}
