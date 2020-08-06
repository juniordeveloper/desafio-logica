<?php declare(strict_types=1);
namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Entities\Person;

use App\Repositories\PersonRepositoryEloquent;
use App\Repositories\TransactionRepositoryEloquent;

use App\Jobs\Notification;

final class TransactionService
{

    /**
    * Repositorio de pessoas
    * @var PersonRepositoryEloquent 
    */
    protected $personRepository;

    /**
    * Repositorio de transaçoes
    * @var TransactionRepositoryEloquent 
    */
    protected $transactionRepository;

    public function __construct(
        PersonRepositoryEloquent $personRepository,
        TransactionRepositoryEloquent $transactionRepository
    )
    {
        $this->personRepository = $personRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Busca validação da transação
     *
     * @param Request $request
     * @return bool
     * 
     * @throws Exception
     */
    public function handle(Request $request) : bool
    {
        $payer = $this->getPerson($request->payer, ['PF']);
        $payee = $this->getPerson($request->payee);
        
        $money = $request->value;
        
        $statusTransaction = $this->saveTransaction(
            $payer,
            $payee,
            $money
        );
        $this->sendNotification();
        return $statusTransaction;
    }

    /**
     * Busca e valida o usuario
     *
     * @param int $id
     * @param array $acceptTypes
     * @return object
     * @throws Exception
     */
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

    /**
     * Valida se o usuario 
     *
     * @param Person $person
     * @param array $types
     * @return bool
     * @throws Exception
     */
    protected function verifyTypePerson(Person $person, array $types) : bool
    {
        if (!in_array($person->type, $types)) {
            throw new \Exception('Pessoa jurídica não pode realizar transações', 400);
        }

        return true;
    }

    /**
     * Busca validação da transação por meio de uma API
     * 
     * @return string
     */
    protected function checkStatusTransferred() : string
    {
        $response = Http::get('https://run.mocky.io/v3/8fafdd68-a090-496f-8c9a-3442cf30dae6');

        if ($response->failed()) {
            return 'TRANSACTION_NOK';
        }
            
        return $this->normalizerResponseTransferred($response->json());
    }

    /**
     * Normaliza a resposta da api
     *
     * @param array $response
     * @return string
     */
    protected function normalizerResponseTransferred(array $response) : string
    {
        $status = \Str::slug($response['message']);
        return $status == 'autorizado' ? 'TRANSACTION_OK' : 'TRANSACTION_NOK';
    }

    /**
     * Salva transação no banco de dados
     *
     * @param array $response
     * @return string
     */
    protected function saveTransaction(Person $payee, Person $payer, float $money) : bool
    {
        $status = $this->checkStatusTransferred();

        try {
            $this->transactionRepository->insert([
                'payee' => $payee->id,
                'payer' => $payer->id,
                'value' => $money,
                'status' => $status
            ]);
        } catch (\Throwable $th) {
            throw new \Exception('Problema ao realizar a transação', 400);
        }
        
        return true;
    }
    
    /**
     * Salva transação no banco de dados
     *
     * @param array $response
     * @return void
     */
    protected function sendNotification()
    {
        Notification::dispatch();
    }
}