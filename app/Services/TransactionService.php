<?php declare(strict_types=1);
namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Entities\Person;

use App\Repositories\PersonRepositoryEloquent;
use App\Repositories\TransactionRepositoryEloquent;

final class TransactionService
{
    protected $personRepository;
    protected $transactionRepository;
    public function __construct(
        PersonRepositoryEloquent $personRepository,
        TransactionRepositoryEloquent $transactionRepository
    )
    {
        $this->personRepository = $personRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function handle(Request $request) : bool
    {
        $request->validate([
            'payer' => 'required|numeric',
            'payee' => 'required|numeric',
            'value' => 'required|numeric|min:0',
        ]);

        $payer = $this->getPerson((int) $request->payer, ['PF']);
        $payee = $this->getPerson((int) $request->payee);

        $statusTransferred = $this->checksStatusTransferred();
        $money = (float) $request->value;
        
        $statusTransaction = $this->saveTransaction(
            $payer,
            $payee,
            $money,
            $statusTransferred,
        );
        return $statusTransaction;
    }

    protected function getPerson(int $id, array $acceptTypes = ['PF', 'PJ']) : object
    {
        try {
            $person = $this->personRepository->findOrFail($id);
        } catch (\Throwable $th) {
            throw new \Exception('Pessoa não encontrada', 400);
        }

        $this->verifyTypePerson($person, $acceptTypes);

        return $person;
    }

    protected function verifyTypePerson(Person $person, array $types) : bool
    {
        if( !in_array($person->type, $types) )
            throw new \Exception('Pessoa jurídica não pode realizar transações', 400);

        return true;
    }

    protected function checksStatusTransferred() : string
    {
        $response = Http::post('https://run.mocky.io/v3/8fafdd68-a090-496f-8c9a-3442cf30dae6');

        if( $response->failed() )
            return 'TRANSACTION_NOK';
            
        return $this->normalizerResponseTransferred($response->json());
    }

    protected function normalizerResponseTransferred(array $response) : string
    {
        $status = \Str::slug($response['message']);
        switch ($status) {
            case 'autorizado':
                return 'TRANSACTION_OK';
                break;
            default:
                return 'TRANSACTION_NOK';
                break;
        }
    }

    protected function saveTransaction(Person $payee, Person $payer, float $money, string $status) : bool
    {
        try {
            $this->transactionRepository->insert([
                'payee' => $payee->id,
                'payer' => $payer->id,
                'value' => $money,
                'status' => $status
            ]);
        } catch (\Throwable $th) {
            dd($th);
            throw new \Exception('Problema ao realizar a transação', 400);
        }
        
        return true;
    }
    
    protected function sendNotification()
    {

    }
}