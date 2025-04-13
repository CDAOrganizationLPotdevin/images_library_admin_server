<?php
namespace App\Controller;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends AbstractController
{
    #[Route('/stats', name: 'app_stats')]
    public function list(EntityManagerInterface $em)
    {

        $lastYear = (new \DateTimeImmutable())->modify('-1 year')->format('Y');
        $last_year = $this->getMonthlyDownloadsForYear((int) $lastYear);
        $currentYear = (new \DateTimeImmutable())->format('Y');
        $current_year = $this->getMonthlyDownloadsForYear((int) $currentYear);

        $best_of_the_day = $this->getTopDownloadsByPeriod('-1 day', 'best_of_the_day');
        $best_of_the_week = $this->getTopDownloadsByPeriod('-7 days', 'best_of_the_week');
        $best_ever = $this->getTopDownloadsByPeriod('-1000 years', 'best_ever'); // tous les temps


        $data = $this->getDataForExport();

        return $this->render('image/stats.html.twig', [
            'best_of_day' => $best_of_the_day['data'],
            'best_of_week' => $best_of_the_week['data'],
            'best_ever' => $best_ever['data'],
            'last_year' => $last_year, // Les données pour l'année actuelle
            'current_year' => $current_year,
        ]);
        
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
            [['id image', 'titre image', 'nombre téléchargement']],
            null, 
            'A1'
        );

        $data = $this->getDataForExport();
        $content=[];

        foreach ($data as $image) {
            $content[] = [
                $image['id'],
                $image['name'],
                $image['downloads'],
            ];
        }

        $sheet->fromArray($content, null, 'A2');

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

    private function getDataForExport():array
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'http://localhost:8002/images?pagination=false');
        $images = $response->toArray()["member"];
        
        $logsResponse = $client->request('GET', 'http://localhost:8002/logs?pagination=false');
        $logs = $logsResponse->toArray()["member"];

        $tabImages=[];

        foreach ($logs as $log) {
            $logDate = new \DateTimeImmutable($log['date']);
            $imageIri = $log['images'] ?? null;
            if ($imageIri) {
                $id = basename($imageIri);
                $tabImages[$id] = ($tabImages[$id] ?? 0) + 1;
            }
        }

        $data = [];

        foreach ($images as $image) {
            if (isset($image['id'])) {
                $data[] = [
                    'id' => $image['id'],
                    'name' => $image['name'],
                    'downloads' => $tabImages[$image['id']]??0, // Placeholder for download count
                ];
            }
        }
        // Sort by downloads in descending order
        usort($data, function ($a, $b) {
            return $b['downloads'] <=> $a['downloads'];
        });


        return array_slice($data, 0, 20, true); // Get top 5 images
    }

    private function getTopDownloadsByPeriod(string $period, string $title): array
    {
        $client = HttpClient::create();
    
        $response = $client->request('GET', 'http://localhost:8002/images?pagination=false');
        $images = $response->toArray()["member"];
        
        // dd($images);

        $imagesById = [];
        foreach ($images as $image) {
            if (isset($image['id'])) {
                $imagesById[$image['id']] = $image;
            }
        }
        $logsResponse = $client->request('GET', 'http://localhost:8002/logs?pagination=false');
        $logs = $logsResponse->toArray()["member"];
    
        $startDate = new \DateTimeImmutable($period);
    
        $downloadCounts = [];
        foreach ($logs as $log) {
            $logDate = new \DateTimeImmutable($log['date']);
            if ($logDate >= $startDate) {
                $imageIri = $log['images'] ?? null;
                if ($imageIri) {
                    $id = basename($imageIri);
                    $downloadCounts[$id] = ($downloadCounts[$id] ?? 0) + 1;
                }
            }
        }
        $finalData = [];
        foreach ($downloadCounts as $id => $count) {
            if (isset($imagesById[$id])) {
                $finalData[$imagesById[$id]['name'] ?? 'Image ' . $id] = $count;
            }
        }
    
        arsort($finalData);
    
        return [
            'title' => $title,  
            'data' => array_slice($finalData, 0, 5, true)  
        ];
    }
    
    


    private function getMonthlyDownloadsForYear(int $year): array
    {
        $client = HttpClient::create();

        $logsResponse = $client->request('GET', 'http://localhost:8002/logs?pagination=false');
        $logs = $logsResponse->toArray()["member"];

        $monthlyDownloads = array_fill(1, 12, 0); 

        foreach ($logs as $log) {
            $logDate = new \DateTimeImmutable($log['date']);
            
            if ($logDate->format('Y') == $year) {
                $month = (int) $logDate->format('m'); 
                
                $monthlyDownloads[$month]++;
            }
        }

        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
            7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        $finalData = [];
        foreach ($months as $monthNumber => $monthName) {
            $finalData[$monthName] = $monthlyDownloads[$monthNumber];
        }

        return $finalData;
    }

}