<?php 

namespace App\Scheduler; 

// src/Scheduler/SendEmailScheduleProvider.php
use App\Scheduler\Message\SendWeeklyMailMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule()]
class SendEmailScheduleProvider implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->with(RecurringMessage::every('5 seconds', new SendWeeklyMailMessage()));
             
            
    }
}



