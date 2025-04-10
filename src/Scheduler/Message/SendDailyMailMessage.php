<?php 

namespace App\Scheduler\Message;

class SendDailyMailMessage
{
    public function __construct(private int $id) {}

    public function getId(): int
    {
        return $this->id;
    }
}