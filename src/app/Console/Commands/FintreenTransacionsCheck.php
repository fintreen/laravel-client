<?php

namespace Fintreen\Laravel\app\Console\Commands;

use App\Models\Fintreen\Fintreen;
use Fintreen\Laravel\app\Models\Fintreen\FintreenModel;
use Illuminate\Console\Command;

class FintreenTransacionsCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fintreen:transactions:check {$localId?} {--limit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks the fintreen transactions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fintreen = new FintreenModel();
        $fintreen->initClient();
        $transacitonIdToCheck =  $this->argument('localId');
        $limit = $this->option('limit');
        if ($transacitonIdToCheck) {
            $transactions = $fintreen->where('id', $transacitonIdToCheck);
        } else {
            $transactions = $fintreen->where('fintreen_status_id', FintreenModel::TRANSACTION_NEW_STATUS);
            if ($limit) {
                $transactions = $transactions->take($limit);
            }
        }
        $transactions = $transactions->get();

        $successCount = 0;
        foreach ($transactions as $fintreenTransaction) {
            // Usage in the application
            /** @var FintreenModel $fintreenTransaction */
            $fintreenTransaction->check(function ($successfullTransaction) use ($successCount) {
                // Code to deposit amount to user balance
                ++$successCount;
            });
        }
        $this->info('Count of successful transactions: ' . $successCount);

        return Command::SUCCESS;
    }
}
