<?php

namespace App\Command;

use App\Service\TopImagesMailer;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'lc:email',
    description: 'Envoie automatiquement les 20 images les plus téléchargées par email.'
)]
class LcEmailCommand extends Command
{
    public function __construct(
        private HttpClientInterface $client,
        private TopImagesMailer $topImagesMailer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Appel au serveur API
        $response = $this->client->request('GET', 'http://127.0.0.1:8002/api/images');
        $data = $response->toArray();

        $images = $data['member']; 

        usort($images, fn($a, $b) => $b['nb_download'] <=> $a['nb_download']);

        $topImages = array_slice($images, 0, 20);

        $this->topImagesMailer->sendTopImagesEmail($topImages);


        $output->writeln("Email envoyé avec les 20 images les plus téléchargées.");

        return Command::SUCCESS;
    }
}



