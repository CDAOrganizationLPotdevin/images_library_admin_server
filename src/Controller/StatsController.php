<?php
namespace App\Controller;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
        $response = $client->request('GET', 'http://localhost:8002/logs?pagination=false');
        $logs=$response->toArray();
        // Date limite = maintenant - 24h
        $now = new \DateTimeImmutable();
        $limit = $now->sub(new \DateInterval('P1D')); // "Period of 1 Day"

        $recentLogs = array_filter($logs['member'], function ($log) use ($limit) {
            $logDate = new \DateTimeImmutable($log['date']);
            return $logDate > $limit;
        });
        echo '<pre>';
        print_r($recentLogs);
        echo '</pre>';
        return $this->render('image/stats.html.twig', [
            'best_of_day' => [
                "image 1"=>9,
                "image 2"=>7,
                "image 3"=>5,
                "image 4"=>2,
                "image 5"=>2,
            ],
            'best_of_week' => [
                "image 1"=>39,
                "image 2"=>37,
                "image 3"=>25,
                "image 4"=>22,
                "image 5"=>15,
            ],
            'best_ever' => [
                "image 1"=>359,
                "image 2"=>337,
                "image 3"=>245,
                "image 4"=>232,
                "image 5"=>195,
            ],
            'last_year' => [
                'Janvier'=>15, 
                'Fevrier'=>20, 
                'Mars'=>25, 
                'Avril'=>35, 
                'Mai'=>50, 
                'Juin'=>70, 
                'Juillet'=>95, 
                'Aout'=>125, 
                'Septembre'=>160, 
                'Octobre'=>200, 
                'Novembre'=>245, 
                'Decembre'=>300, 
            ],
        ]);
        // return $this->render('image/stats.html.twig', [
        //     'best_of_day' => $best_of_the_day,
        //     'best_of_week' => $best_of_the_week,
        //     'best_ever' => $best_ever,
        //     'last_year' => $last_year,
        // ]);
        
    }
    #[Route('/export-excel', name: 'export_excel')]
    public function exportExcel(): StreamedResponse
    {
        if (ob_get_contents()) {
            ob_end_clean();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [['id image', 'titre image', 'nombre téléchargement', 'nombre ouvertures']],
            null, 
            'A1'
        );

        $data = [
            ['1', 'image 1', "23","245"],
            ['2', 'image 2', "23","245"],
            ['3', 'image 3', "23","245"],
            ['4', 'image 4', "23","245"],
            ['5', 'image 5', "23","245"],
            ['6', 'image 6', "23","245"],
            ['7', 'image 7', "23","245"],
            ['8', 'image 8', "23","245"],
            ['9', 'image 9', "23","245"],
            ['10', 'image 10', "23","245"],
        ];

        $sheet->fromArray($data, null, 'A2');

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'stats.xlsx'
        );

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
