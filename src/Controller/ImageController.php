<?php
namespace App\Controller;

use App\Form\ImageType;
use App\Form\DeleteImageType;
use Symfony\Component\HttpClient\HttpClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{
    // Route permettant l'affichage des images
    #[Route('/', name: 'app_image')]
    public function list(EntityManagerInterface $em)
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'http://localhost:8002/api/images?pagination=false');
        $images=$response->toArray()["member"];
        return $this->render('image/list.html.twig', ['images' => $images]);
    }
    // Route permettant l'édition d'une image par id
    #[Route('/image/edit/{id}', name: 'image_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em)
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'http://localhost:8002/api/images/'.$id);
        $image=$response->toArray();

        $form = $this->createForm(ImageType::class, $image);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $client = HttpClient::create();
            $response = $client->request('PATCH', 'http://localhost:8002/api/images/'.$data['id'], [
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ],
                'json' => [
                    'name' => $data['name']
                ],
            ]);

            
            $response = $client->request('GET', 'http://localhost:8002/api/images?pagination=false');
            $images=$response->toArray()["member"];
            
            return $this->redirectToRoute('app_image', ['images' => $images]);
        }

        return $this->render('image/edit.html.twig', [
            'image'=>$image,
            'form' => $form->createView(),
        ]);
    }

    // Route permettant la suppression d'une image par id
    #[Route('/image/delete/{id}', name: 'image_delete')]
    public function delete(int $id, Request $request, EntityManagerInterface $em)
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'http://localhost:8002/api/images/'.$id);
        $image=$response->toArray();

        $form = $this->createForm(DeleteImageType::class, $image);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $client = HttpClient::create();
            $response = $client->request('PATCH', 'http://localhost:8002/api/images/'.$data['id'], [
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ],
                'json' => [
                    'is_deleted' => true
                ],
            ]);

            $response = $client->request('GET', 'http://localhost:8002/api/images?pagination=false');
            $images=$response->toArray()["member"];

            return $this->redirectToRoute('app_image', ['images' => $images]);
        }
        return $this->render('image/delete.html.twig', [
            'image'=>$image,
            'form' => $form->createView(),
        ]);
    }
    
}
