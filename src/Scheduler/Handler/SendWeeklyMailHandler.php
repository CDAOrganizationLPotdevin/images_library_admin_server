<?php 


namespace App\Scheduler\Handler;

use App\Scheduler\Message\SendWeeklyMailMessage;
use App\Service\TopImagesProcessor;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendWeeklyMailHandler
{
    private KernelInterface $kernel;

    // public function __construct(KernelInterface $kernel)
    // {
    //     $this->kernel = $kernel;
        
    // }

    public function __construct(
        private TopImagesProcessor $processor
    ) {}

    // public function __invoke(SendWeeklyMailMessage $message )
    // {
    //     $application = new Application($this->kernel);
    //     $input = new ArrayInput([
    //         'command' => 'message:weekly'
    //     ]);
    //     $output = new BufferedOutput();
    //     $application->run($input, $output);
    // }

    public function __invoke(SendWeeklyMailMessage $message): void
    {
        $this->processor->processAndSend();
    }
}
