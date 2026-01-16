<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupApprovalChainDefault extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-approval-chain-default';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup default approval chain configurations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Logic to setup default approval chain configurations

            


            $this->info('Default approval chain configurations have been set up successfully.');
            return Command::SUCCESS;

            //code...
        } catch (\Throwable $th) {
            //throw $th;
            $this->error('An error occurred: ' . $th->getMessage());
            return Command::FAILURE;
        }
    }
}
