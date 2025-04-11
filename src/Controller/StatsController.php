<?php
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends AbstractController
{
    #[Route('/stats', name: 'app_stats')]
    public function list(EntityManagerInterface $em)
    {
        $client = HttpClient::create();
        // dd($client);
        $response = $client->request('GET', 'http://localhost:8002/api/images');
        $images=$response->toArray()["member"];
        return $this->render('image/stats.html.twig', ['images' => $images]);
    }
}
