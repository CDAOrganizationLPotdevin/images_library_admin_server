<?php 


namespace App\Scheduler\Handler;

use App\Scheduler\Message\SendDailyMailMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendDailyMailHandler
{
    public function __invoke(SendDailyMailMessage $message)
    {
        // ... do some work to send the report to the customers
    }
}