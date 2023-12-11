<?php

namespace Fintreen\Laravel\app\Console\Commands;

use App\Models\Fintreen\Fintreen;
use Illuminate\Console\Command;

class FintreenTransacionsCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fintreen:transactions:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $transactions = Fintreen::where('fintreen_status_id')->get();
        foreach ($transactions as $transaction) {

        }


        return Command::SUCCESS;
    }
}
