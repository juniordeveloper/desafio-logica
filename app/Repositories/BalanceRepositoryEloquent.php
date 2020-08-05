<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\BalanceRepository;
use App\Entities\Balance;
use App\Validators\BalanceValidator;

/**
 * Class BalanceRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class BalanceRepositoryEloquent extends BaseRepository implements BalanceRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Balance::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
