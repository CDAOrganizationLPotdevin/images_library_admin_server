<?php 

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TopImagesProcessor
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private TopImagesMailer $mailer
    ) {}

    public function processAndSend(): void
    {
        $response = $this->httpClient->request('GET', 'https://127.0.0.1:8002/api/images');

        $apiresponses = $response->toArray();
        $images = $apiresponses ['member']; 

        $topImages = array_slice($images, 0, 20);

        $this->mailer->sendTopImagesEmail($topImages);
    }
}

