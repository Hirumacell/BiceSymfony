<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class InterventionController extends AbstractController
{
    private $httpClient;


    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $API = $this->getParameter('app.API');
        $url = $API . "/GetAllInterventions";
        $response = $this->httpClient->request('GET', $url);
        //Si y'a qqch
        if ($response->getStatusCode() === Response::HTTP_OK)
        {
            $data = $response->getContent();
            $dataArray = json_decode($data, true);
            return $this->render('intervention/index.html.twig', ['data' => $dataArray,]);
        }
    }

    #[Route('/ajouter/intervention/', name: 'AjoutIntervention')]
    public function AjoutIntervention(Request $request)
    {
        $API = $this->getParameter('app.API');
        $url = $API . "/GetAllVehicule";

        $requet = $this->httpClient->request('GET', $url);
        $data = $requet->getContent();
        $dataArray = json_decode($data, true);
        $idarray = array_column($dataArray, 'id');

        if ($request->isMethod('POST'))
        {
            $vehiculeId = [];
            $nb_vehicule = count($dataArray) - 1;
            $test = 1;

            for ($i = 0; $i <= $nb_vehicule; $i++)
            {
                $a = $idarray[$i];
                if ($request->request->get($a) === '1')
                {
                    $b = $idarray[$i];
                    array_push($vehiculeId, $b);
                    $test ++;
                }
            }

            $data = [
                'nom' => $request->request->get('nom'),
                'description' => $request->request->get('description'),
                'date_intervention' => $request->request->get('date_intervention'),
                'vehiculeId' => $vehiculeId,
                ];

            // Envoi des données JSON à l'API
            $url2 = $API . "/Ajouter";
            $response = $this->httpClient->request('POST', $url2, [
                'json' => $data
            ]);

            if ($response->getStatusCode() === Response::HTTP_OK) {
                // Retourne une réponse JSON de succès
                return $this->redirectToRoute('app_home');

            } else {
                // Retourne une réponse JSON d'erreur
                return new JsonResponse(['error' => 'Une erreur s\'est produite lors de l\'ajout de l\'intervention.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        }

        return $this->render('intervention/ajouterintervention.html.twig', ['data' => $dataArray]);
    }

    #[Route('/ajouter/vehicule', name: 'AjoutVehicule')]
    public function AjoutVehicule2(Request $request)
    {
        if ($request->isMethod('POST')) {
            $API = $this->getParameter('app.API');
            $url = $API . "/GestionVehicule/Ajouter";

            $data = [
                'nom' => $request->request->get('nom'),
                'immatriculation' => $request->request->get('immatriculation'),
                'numero_interne' => $request->request->get('numero_interne'),
            ];

            // Valide les données si nécessaire

            // Envoie les données JSON à l'API
            $response = $this->httpClient->request('POST', $url, [
                'json' => $data,
            ]);

            if ($response->getStatusCode() === Response::HTTP_OK) {
                // Retourne une réponse JSON de succès
                return new JsonResponse(['success' => true]);
            } else {
                // Retourne une réponse JSON d'erreur
                return new JsonResponse(['error' => 'Une erreur s\'est produite lors de l\'ajout du véhicule.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        }
        return $this->render('vehicule/AjoutVehicule.html.twig');
    }

    #[Route('/intervention/{id}', name: 'vehicule_en_intervention')]
    public function getInterventionDetails($id): Response
    {
        $API = $this->getParameter('app.API');
        $url = $API . "/GetVehiculeByIdIntervention/";
        $url2 = $API . "/GetInterventionById/";

        $response = $this->httpClient->request('GET', $url . $id);
        $response2 = $this->httpClient->request('GET', $url2 . $id);

        //Si y'a qqch
        if ($response->getStatusCode() === Response::HTTP_OK)
        {
            $data = $response->getContent();
            $data2 = $response2->getContent();
            $dataArray = json_decode($data, true);
            $dataArray2 = json_decode($data2, true);


            return $this->render('vehicule/vehiculeintervention.html.twig', ['data' => $dataArray,
                'data2' => $dataArray2,
                'idInter' => $id]);
        }
    }

    #[Route('/intervention/{idInter}/{idVehicule}', name: 'MaterielVehicule')]
    public function getmaterieldansvehicule($idInter, $idVehicule)
    {
        $API = $this->getParameter('app.API');
        $url = $API . "/GetInterventionById/";
        $url2 = $API . "/GetVehiculeById/";

        $response = $this->httpClient->request('GET', $API . '/GetMaterielByIdInterventionAndIdVehicule?vehiculeId='. $idVehicule . '&interventionId='. $idInter);
        $response2 = $this->httpClient->request('GET', $url . $idInter);
        $response3 = $this->httpClient->request('GET', $url2 . $idVehicule);
        //Si y'a qqch
        if ($response->getStatusCode() === Response::HTTP_OK)
        {
            $data = $response->getContent();
            $data2 = $response2->getContent();
            $data3 = $response3->getContent();
            $dataArray = json_decode($data, true);
            $dataArray2 = json_decode($data2, true);
            $dataArray3 = json_decode($data3, true);
            return $this->render('intervention/materielvehicule.html.twig', ['data' => $dataArray,
                'data2' => $dataArray2,
                'data3' => $dataArray3,
                'idInter' => $idInter,
                'idVehicule' => $idVehicule]);
        }
    }

    #[Route('/retour_intervention/{idInter}/{idVehicule}', name: 'RetourIntervention')]
    public function Retourintervention($idInter, $idVehicule)
    {
        $API = $this->getParameter('app.API');
        $url = $API . "/GetInterventionById/";
        $url2 = $API . "/GetVehiculeById/";

        $response2 = $this->httpClient->request('GET',  $url . $idInter);
        $response3 = $this->httpClient->request('GET', $url2 . $idVehicule);
        //Si y'a qqch
        if ($response2->getStatusCode() === Response::HTTP_OK)
        {
            $data2 = $response2->getContent();
            $data3 = $response3->getContent();
            $dataArray2 = json_decode($data2, true);
            $dataArray3 = json_decode($data3, true);
            return $this->render('intervention/retourintervention.html.twig', [
                'data2' => $dataArray2,
                'data3' => $dataArray3,
                'idInter' => $idInter,
                'idVehicule' => $idVehicule]);
        }
    }

    #[Route('/retour_intervention/retour', name: 'RetourInterventionForm')]
    public function RetourInterventionForm(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $API = $this->getParameter('app.API');
            $url = $API . "/RetourIntervention";

            $file1 = $request->files->get('file1');
            $file2 = $request->files->get('file2');
            $interventionid = $request->get('interventionid');
            $vehiculeid = $request->get('vehiculeid');

            $content1 = file_get_contents($file1->getPathname());
            $file1Lines = explode(PHP_EOL, $content1);
            $file1List = array_map('strval', $file1Lines);

            $content2 = file_get_contents($file2->getPathname());
            $file2Lines = explode(PHP_EOL, $content2);
            $file2List = array_map('strval', $file2Lines);

            $data2 = [
                'interventionId'=>$interventionid,
                'vehiculeId'=>$vehiculeid,
                'listecodebarreutilisé' => $file1List,
                'listecodebarrenonutilisé'=> $file2List,
            ];

            // Envoi des données JSON à l'API
            $response = $this->httpClient->request('POST', $url, [
                'json' => $data2
            ]);

            if ($response->getStatusCode() === Response::HTTP_OK) {
                // Retourne une réponse JSON de succès
                return $this->redirectToRoute(
                    'MaterielVehicule',
                    array(
                        'id'=> $interventionid,
                        'id2'=>$vehiculeid
                    ));
                //return new JsonResponse(['success' => true]);

            } else {
                // Retourne une réponse JSON d'erreur
                //var_dump($data2);
                return new JsonResponse(['error'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        }
    }

    #[Route('/gerer/vehicule', name: 'app_vehicule')]
    public function indexVehicule(): Response
    {
        $API = $this->getParameter('app.API');
        $url = $API . "/GetAllVehicule";

        //$this->getParameter("hhh");
        $response = $this->httpClient->request('GET', $url);
        //Si y'a qqch
        if ($response->getStatusCode() === Response::HTTP_OK)
        {
            $data = $response->getContent();
            $dataArray = json_decode($data, true);
            return $this->render('vehicule/index.html.twig', ['data' => $dataArray]);
        }
    }
}
