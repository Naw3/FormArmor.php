<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ListeClientsController extends AbstractController
{
    #[Route('/clients', name: 'clients_list')]
    public function list(ClientRepository $clientRepository)
    {
        $clients = $clientRepository->findAll();

        return $this->render('admin/listeClients.html.twig', [
            'clients' => $clients
        ]);
    }

    #[Route('/liste', name: 'client_info_ajax', methods: ['GET'])]
    public function getClientInfo(Request $request, ClientRepository $clientRepository): JsonResponse
    {
        $clientId = $request->query->get('id'); 
        $client = $clientRepository->find($clientId);

        if (!$client) {
            return new JsonResponse(['error' => 'Client non trouvé'], 404);
        }

        return new JsonResponse([
            'nom' => $client->getNom(),
            'prenom' => $client->getPrenom(),
            'heuresCompta' => $client->getNbhcompta(),
            'heuresBureautique' => $client->getNbhbur()
        ]);
    }
}