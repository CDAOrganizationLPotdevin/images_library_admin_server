<?php
namespace App\Controller;

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
        $client = HttpClient::create();
        // dd($client);
        $response = $client->request('GET', 'http://localhost:8002/images');
        $images=$response->toArray()["member"];
        return $this->render('image/stats.html.twig', ['images' => $images]);
    }

    private function getTopDownloadsByPeriod($period)
{
    // Création du client HTTP pour récupérer les images
    $client = HttpClient::create();
    $response = $client->request('GET', 'http://localhost:8001/images');
    $images = $response->toArray()["member"];
    
    // Création du client HTTP pour récupérer les logs
    $logsResponse = $client->request('GET', 'http://localhost:8001/logs');
    $logs = $logsResponse->toArray()["member"];
    
    // Regrouper les données par image (ID)
    $downloadsById = [];
    foreach ($images as $image) {
        if (isset($image['id']) && isset($image['nb_download'])) {
            $downloadsById[$image['id']] = [
                'nb_download' => $image['nb_download'],
                'logs' => []
            ];
        }
    }

    // Ajouter les logs associés à chaque image
    foreach ($logs as $log) {
        $imageId = $log['images'];
        $id = basename($imageId);
        if (isset($downloadsById[$id])) {
            $downloadsById[$id]['logs'][] = [
                'date' => $log['date'],
            ];
        }
    }
    
    // Combiner ID, nb_download et date des logs
    $finalData = [];
    foreach ($downloadsById as $id => $data) {
        foreach ($data['logs'] as $log) {
            $finalData[] = [
                'id' => $id,
                'nb_download' => $data['nb_download'],
                'date' => $log['date']
            ];
        }
    }

    // Filtrage des données selon la période
    $filteredData = [];
    $startDate = new \DateTime($period);
    foreach ($finalData as $entry) {
        $logDate = new \DateTime($entry['date']);
        if ($logDate >= $startDate) {
            $filteredData[] = $entry;
        }
    }

    // Trier les données par nb_download décroissant
    usort($filteredData, function($a, $b) {
        return $b['nb_download'] <=> $a['nb_download'];
    });

    // Retourner les top 5
    return array_slice($filteredData, 0, 5);
}



#[Route('/stats/top5day', name: 'app_stats_top5day')]
public function listTop5Day()
{
    // Appel de la fonction réutilisable pour obtenir les top 5 des téléchargements du jour
    $top5Day = $this->getTopDownloadsByPeriod('-1 day');
    
    // Retourner la réponse en JSON
    return $this->json($top5Day);
}

#[Route('/stats/top5week', name: 'app_stats_top5week')]
public function listTop5Week()
{
    // Appel de la fonction réutilisable pour obtenir les top 5 des téléchargements de la semaine
    $top5Week = $this->getTopDownloadsByPeriod('-7 days');
    
    // Retourner la réponse en JSON
    return $this->json($top5Week);
}

#[Route('/stats/top5year', name: 'app_stats_top5year')]
public function listTop5Year()
{
    // Appel de la fonction réutilisable pour obtenir les top 5 des téléchargements de l'année
    $top5Year = $this->getTopDownloadsByPeriod('-1 year');
    
    // Retourner la réponse en JSON
    return $this->json($top5Year);
}







    // #[Route('/stats/top5week', name: 'app_stats_top5week')]
    // public function listTop5Week()
    // {
    //     // Création du client HTTP pour récupérer les images
    //     $client = HttpClient::create();
    //     $response = $client->request('GET', 'http://localhost:8001/images');
    //     $images = $response->toArray()["member"];
        
    //     // Création du client HTTP pour récupérer les logs (assume que les logs sont dans une autre API)
    //     $logsResponse = $client->request('GET', 'http://localhost:8001/logs');
    //     $logs = $logsResponse->toArray()["member"];;
        
    //     // On va créer un tableau associatif pour regrouper les données par image (ID)
    //     $downloadsById = [];
        
    //     // Récupérer les données de téléchargement (nb_download par image)
    //     foreach ($images as $image) {
    //         if (isset($image['id']) && isset($image['nb_download'])) {
    //             $downloadsById[$image['id']] = [
    //                 'nb_download' => $image['nb_download'],
    //                 'logs' => [] // Initialiser l'array pour les logs associés
    //             ];
    //         }
    //     }
    
    //     // Ajouter les logs associés à chaque image
    //     foreach ($logs as $log) {
    //         $imageId = $log['id'];  // Assurez-vous que l'ID de l'image est dans les logs
    //         if (isset($downloadsById[$imageId])) {
    //             $downloadsById[$imageId]['logs'][] = [
    //                 'date' => $log['date'],
    //             ];
    //         }
    //     }
        
    //     // On crée maintenant un tableau qui contient ID, nb_download et date des logs
    //     $finalData = [];
    //     foreach ($downloadsById as $id => $data) {
    //         foreach ($data['logs'] as $log) {
    //             $finalData[] = [
    //                 'id' => $id,
    //                 'nb_download' => $data['nb_download'],
    //                 'date' => $log['date']
    //             ];
    //         }
    //     }
    
    //     // Filtrage des données par période (ici, on prend les 7 derniers jours par exemple)
    //     $filteredData = [];
    //     $startDate = new \DateTime('-7 days'); // Période des 7 derniers jours
    //     foreach ($finalData as $entry) {
    //         $logDate = new \DateTime($entry['date']);
    //         if ($logDate >= $startDate) {
    //             $filteredData[] = $entry;
    //         }
    //     }
    
    //     // Trier par nb_download décroissant pour obtenir les images les plus téléchargées
    //     usort($filteredData, function($a, $b) {
    //         return $b['nb_download'] <=> $a['nb_download'];
    //     });
    
    //     // Récupérer les 5 premières images
    //     $top5Images = array_slice($filteredData, 0, 5);
    
    //     // Retourner la réponse en JSON avec les 5 images les plus téléchargées
    //     return $this->json($top5Images);
    // }
    











//     public function listNbDownload()
// {
//     $client = HttpClient::create();
//     $response = $client->request('GET', 'http://localhost:8001/images');
//     $images=$response->toArray()["member"];
    
//     $downloads = [];
//     foreach ($images as $image) {
//         if (isset($image['nb_download'])) {
//             $downloads[] = $image['nb_download'];
//         }
//     }
//     dd($downloads); 
//     // Retourner les données à la vue
//     return $this->json($downloads);

// }

//     private function getLogsFromApi(): array
//     {
//         $client = HttpClient::create();
        
//         // Récupérer les logs depuis l'API
//         $logResponse = $client->request('GET', 'http://localhost:8001/logs');
//         $logs = $logResponse->toArray()["member"]; // ou adapte selon la structure réelle
    
//         // dd($logs);
//         return $logs;
//     }

    
//     private function getTopLogsForToday(): array
//     {
//         $logs = $this->getLogsFromApi();  // Récupère tous les logs de l'API
//         $downloads = $this->listNbDownload();
//         // dd($downloads);
    
//         // Vérifie que les dates sont bien au bon format
//         $cutoff = new \DateTimeImmutable('-24 hours');
    
//         // Filtrer les logs des dernières 24 heures
//         $logs = array_filter($logs, function ($log) use ($cutoff) {
//             if (isset($log['date'])) {
//                 // Si la date est au format 'Y-m-d H:i:s'
//                 $logDate = new \DateTimeImmutable($log['date']); // Convertir en objet DateTimeImmutable
//                 // dd($log['date']);
//                 return $logDate >= $cutoff;
//             }
//             return false;
//         });

        
//         // Regrouper les logs par image_id pour savoir combien de fois chaque image a été téléchargée
//         $downloadsPerImage = [];
//         foreach ($logs as $log) {
//             $imageId = $log['id'] ?? null;
//             // dd($imageId);
//             if ($imageId !== null) {
//                 $downloadsPerImage[$imageId] = ($downloadsPerImage[$imageId] ?? 0) + 1;
//             }
//         }
    
//         // Trier les images par nombre de téléchargements décroissant
//         arsort($downloadsPerImage);
//         // Limiter à 5 résultats
//         $top5 = array_slice($downloadsPerImage, 0, 5, true); // garde les clés (image_id)
//         // Transformer en un tableau plus simple à utiliser dans la vue
//         $top5Formatted = [];
//         foreach ($top5 as $imageId => $downloadsCount) {
//             $top5Formatted[] = [
//                 'image_id' => $imageId,
//                 'downloads_count' => $downloadsCount
//             ];
//         }
//         // dd($top5Formatted);
//         return $top5Formatted;  // Renvoyer un tableau simple avec image_id et downloads_count
    
//     }

// // private function getTopLogsForWeek(): array
// // {
// //     $logs = $this->getLogsFromApi();  

// //     $cutoff = new \DateTimeImmutable('-168 hours');

// //     $logs = array_filter($logs, function ($log) use ($cutoff) {
// //         return new \DateTimeImmutable($log['createdAt']) >= $cutoff;
// //     });

// //     $downloadsPerImage = [];
// //     foreach ($logs as $log) {
// //         $imageId = $log['image_id'] ?? null;
// //         if ($imageId !== null) {
// //             $downloadsPerImage[$imageId] = ($downloadsPerImage[$imageId] ?? 0) + 1;
// //         }
// //     }

// //     arsort($downloadsPerImage);

// //     return array_slice($downloadsPerImage, 0, 5, true); 
// // }


// private function getTopLogsForYear(): array
// {
//     $logs = $this->getLogsFromApi(); 

//     // Filtrer les logs des 365 derniers jours
//     $cutoff = new \DateTimeImmutable('-365 days');

//     $logs = array_filter($logs, function ($log) use ($cutoff) {
//         return new \DateTimeImmutable($log['createdAt']) >= $cutoff;
//     });

//     $downloadsPerImage = [];
//     foreach ($logs as $log) {
//         $imageId = $log['image_id'] ?? null;
//         if ($imageId !== null) {
//             $downloadsPerImage[$imageId] = ($downloadsPerImage[$imageId] ?? 0) + 1;
//         }
//     }

//     arsort($downloadsPerImage);

//     return array_slice($downloadsPerImage, 0, 5, true); 
// }



// #[Route('/stats/top5day', name: 'app_stats_top5_day')]
// public function top5Day(): Response 
// {
//     $top5 = $this->getTopLogsForToday();  

//     dd($top5); 

//     return $this->render('image/top5day.html.twig', [
//         'top5' => $top5,
//     ]);
// }
 



    
    // #[Route('/stats/top5day', name: 'app_stats_top5_day')]
    // public function top5Day(): Response 
    // {
    //     $client = HttpClient::create();
    //     $response = $client->request('GET', 'http://localhost:8001/logs'); // <-- adapte l'URL ici
    //     $logs = $response->toArray()["member"]; // <-- adapte si le JSON a une autre structure
    
    //     $now = new \DateTimeImmutable();
    //     $cutoff = $now->modify('-24 hours');
    
    //     // Étape 1 : filtrer les logs < 24h
    //     $recentLogs = array_filter($logs, function ($log) use ($cutoff) {
    //         return new \DateTimeImmutable($log['date']) >= $cutoff;
    //     });
    
    //     // Étape 2 : grouper par image_id
    //     $downloadsPerImage = [];
    
    //     foreach ($recentLogs as $log) {
    //         $imageId = $log['image_id'];
    
    //         if (!isset($downloadsPerImage[$imageId])) {
    //             $downloadsPerImage[$imageId] = 0;
    //         }
    
    //         $downloadsPerImage[$imageId]++;
    //     }
    
    //     // Étape 3 : trier par nombre de téléchargements décroissant
    //     arsort($downloadsPerImage); // garde les clés associées
    
    //     // Étape 4 : on prend les 5 premiers
    //     $top5 = array_slice($downloadsPerImage, 0, 5, true); // true = conserve les clés
    
    //     // Si tu veux aussi les infos des images, tu peux appeler l'API des images ici
    //     // et matcher image_id avec les infos
    
    //     dd($top5);

    //     return $this->render('image/stats.html.twig', [
    //         'top5' => $top5,
    //     ]);

    // }
     
    
    


    // #[Route('/stats/top5week', name: 'app_stats_top5_week')]


    // #[Route('/stats/top5ever', name: 'app_stats_top5_ever')]
    // #[Route('/stats/top5year', name: 'app_stats_top5_year')]
}