<?php
declare(strict_types=1);

namespace  Fintreen\Laravel\app\Events;

use Fintreen\Laravel\app\Models\Fintreen\FintreenModel;
use Illuminate\Foundation\Events\Dispatchable;

class FintreenTransactionIsSuccess {

    use Dispatchable;

    public FintreenModel $transaction;

    public function __construct(FintreenModel $transaction)
    {
        $this->transaction = $transaction;
    }
}