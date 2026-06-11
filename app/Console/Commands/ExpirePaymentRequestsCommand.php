<?php

namespace App\Console\Commands;

use App\Actions\ExpirePendingPaymentRequestsAction;
use Illuminate\Console\Command;

class ExpirePaymentRequestsCommand extends Command
{
    protected $signature = 'payments:expire';

    protected $description = 'Expire pending payment requests created more than 48 hours ago.';

    public function handle(ExpirePendingPaymentRequestsAction $expirePaymentRequests): int
    {
        $expired = $expirePaymentRequests->execute();

        $this->info("Expired {$expired} pending payment request(s).");

        return self::SUCCESS;
    }
}
