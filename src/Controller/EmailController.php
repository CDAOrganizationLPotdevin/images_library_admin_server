<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use App\Command\LcEmailCommand;
use App\Service\TopImagesMailer;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpKernel\KernelInterface;

class EmailController extends AbstractController
{

    public function __construct(private LcEmailCommand $lcEmailCommand) {}

    // Route permettant l'envoie du mail des images les plus télécharger
    #[Route('/send-top-images-email', name: 'send_top_images_email')]
    public function sendTopImagesEmail(TopImagesMailer $topImagesMailer): Response
    {
        $client = HttpClient::create();

        $response = $client->request('GET', 'http://127.0.0.1:8002/api/images')->toArray();
        $images = $response['member'];
        
        usort($images, function ($a, $b) {
            if (is_array($a) && is_array($b) && isset($a['nb_download'], $b['nb_download'])) {
                return $b['nb_download'] <=> $a['nb_download'];
            }
            return 0; // If not, keep original order
        });
        $topImages = array_slice($images, 0, 20);
        $topImagesMailer->sendTopImagesEmail($topImages);

        return new Response("Email envoyé avec les 20 images les plus téléchargées.");
    }


    // Route permettant l'envoie du mail des statistiques
    #[Route('/send-email', name: 'send_email', methods: ['POST'])]
    public function sendTopImages(HttpFoundationRequest $request, KernelInterface $kernel): RedirectResponse
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $returnCode = $this->lcEmailCommand->run($input, $output);
        $result = $output->fetch();

        if ($returnCode === Command::SUCCESS) {
            $this->addFlash('success', 'Email envoyé avec succès !');
        } else {
            $this->addFlash('danger', 'Erreur lors de l’envoi de l’email.');
        }

        return $this->redirectToRoute('app_stats');
    }

}


