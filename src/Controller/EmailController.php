<?php 

namespace App\Controller;

use App\Service\TopImagesMailer;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmailController extends AbstractController
{
    #[Route('/send-top-images-email', name: 'send_top_images_email')]
    public function sendTopImagesEmail(TopImagesMailer $topImagesMailer): Response
    {
        $client = HttpClient::create();

        $response = $client->request('GET', 'https://127.0.0.1:8001/api/images');
        $images = $response->toArray();

        usort($images, fn($a, $b) => $b['nb_download'] <=> $a['nb_download']);
        $topImages = array_slice($images, 0, 20);

        $topImagesMailer->sendTopImagesEmail($topImages);

        return new Response("Email envoyé avec les 20 images les plus téléchargées.");
    }
}


