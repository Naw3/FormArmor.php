<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Nécessaire pour la pagination
use Symfony\Component\HttpFoundation\Request; // Nous avons besoin d'accéder à la requête pour obtenir le numéro de page
use Knp\Component\Pager\PaginatorInterface; // Nous appelons le bundle KNP Paginator

use App\Entity\Client;
use App\Entity\Statut;
use App\Entity\Formation;
use App\Entity\SessionFormation;
use App\Entity\PlanFormation;

use App\Form\ClientType;
use App\Form\ClientCompletType;
use App\Form\StatutType;
use App\Form\FormationType;
use App\Form\SessionType;
use App\Form\PlanFormationType;

use App\Repository\ClientRepository;
use App\Repository\StatutRepository;
use App\Repository\FormationRepository;
use App\Repository\SessionFormationRepository;
use App\Repository\PlanFormationRepository;

use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdminController extends AbstractController
{
	#[Route('/admin', name: 'admin')]
    // Affichage de la vue admin ou du formulaire d'authentification
	public function authentif(Request $request, ManagerRegistry $doctrine, AuthenticationUtils $authenticationUtils)
    {
		// On récupère l'utilisateur authentifié
		$user = $this->getUser();
		//var_dump($user);
		// On controle si quelqu'un est connecté et possède le rôle ADMIN
		if ($user && (in_array("ROLE_ADMIN", $user->getRoles())))
		{
			return $this->render('Admin/accueil.html.twig');
		}
		else
		{
			// Sinon on redirige vers la connexion
			// get the login error if there is one
			$error = $authenticationUtils->getLastAuthenticationError();
			// last username entered by the user
			$lastUsername = $authenticationUtils->getLastUsername();
			return $this->redirectToRoute('login');
		}
    }

	#[Route('/admin/statut/liste', name: 'adminStatutListe')]
	// Gestion des statuts
	public function listeStatut(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine)
	{
		// On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');

		$em = $doctrine->getManager();
		$rep = $em->getRepository(Statut::class);

		$lesStatuts = $rep->findAll();
		
		$lesStatutsPagines = $paginator->paginate(
            $lesStatuts, // Requête contenant les données à paginer (ici nos statuts)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            4 // Nombre de résultats par page
        );
				
		return $this->render('Admin/statut.html.twig', Array('lesStatuts' => $lesStatutsPagines));
    }

	#[Route('/admin/statut/modif/{id}', name: 'adminStatutModif')]
	// Affichage du formulaire de modification d'un statut
	public function modifStatut($id, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine)
    {
		// On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');

		$em = $doctrine->getManager();
		$rep = $em->getRepository(Statut::class);
		
		$statut = $rep->find($id);
		
		
		// Création du formulaire à partir du statut récupéré
		$form = $this->createForm(StatutType::class, $statut);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			if ($form->isValid())
			{
				// mise à jour de la bdd
				//$em->persist($statut);
				//$em->flush();
				$doctrine->getManager()->persist($statut);
				$doctrine->getManager()->flush();

				// Réaffichage de la liste des statuts
				$lesStatuts = $rep->listeStatuts();
				$lesStatutsPagines = $paginator->paginate(
					$lesStatuts, // Requête contenant les données à paginer (ici nos statuts)
					$request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
					4 // Nombre de résultats par page
				);
				return $this->render('Admin/statut.html.twig', Array('lesStatuts' => $lesStatutsPagines));
			}
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('Admin/formStatut.html.twig', array('form' => $form->createView(), 'action' => 'modification'));
    }

	#[Route('/admin/statut/supp/{id}', name: 'adminStatutSupp')]
	public function suppStatut($id, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine) // Affichage du formulaire de suppression d'un statut
    {
		// On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');

		$em = $doctrine->getManager();
		$rep = $em->getRepository(Statut::class);

		$statut = $rep->find($id);
		
		// Création du formulaire à partir du statut récupéré
		$form = $this->createForm(StatutType::class, $statut);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			
			// mise à jour de la bdd
			$res = $rep->suppStatut($id);
			$doctrine->getManager()->persist($statut);
			$doctrine->getManager()->flush();
				
			// Réaffichage de la liste des statuts
			$lesStatuts = $rep->listeStatuts();
			$lesStatutsPagines = $paginator->paginate(
				$lesStatuts, // Requête contenant les données à paginer (ici nos statuts)
				$request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
				4 // Nombre de résultats par page
			);
			return $this->render('Admin/statut.html.twig', Array('lesStatuts' => $lesStatutsPagines));
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('Admin/formStatut.html.twig', array('form' => $form->createView(), 'action' => 'suppression'));
    }

	#[Route('/admin/client/liste', name: 'adminClientListe')]
	// Gestion des clients
	public function listeClient(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine)
	{
		// On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		
		$em=$doctrine->getManager();
		$rep = $em->getRepository(Client::class);
		$lesClients = $rep->findAll();
		
		$lesClientsPagines = $paginator->paginate(
            $lesClients, // Requête contenant les données à paginer (ici nos clients)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            4 // Nombre de résultats par page
        );
				
		return $this->render('Admin/client.html.twig', Array('lesClients' => $lesClientsPagines));
	}
	/**
     * @Route("/admin/client/modif/{id}", name="adminClientModif")
     */
	// Affichage du formulaire de modification d'un client
	public function modifClient($id, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine) // Affichage du formulaire de modification d'un client
    {
		// On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');

		// Récupération du client d'identifiant $id
		$this->doctrine=$doctrine;
		$rep = $doctrine->getRepository(Client::class);
		$client = $rep->find($id);
		
		
		// Création du formulaire à partir du client récupéré
		$form = $this->createForm(ClientCompletType::class, $client);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			if ($form->isValid())
			{
				// mise à jour de la bdd
				$doctrine->getManager()->persist($client);
				$doctrine->getManager()->flush();
				
				// Réaffichage de la liste des clients
				$lesClients = $rep->listeClients();
				$lesClientsPagines = $paginator->paginate(
					$lesClients, // Requête contenant les données à paginer (ici nos clients)
					$request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
					4 // Nombre de résultats par page
				);
				return $this->render('Admin/client.html.twig', Array('lesClients' => $lesClientsPagines));
			}
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('Admin/formClient.html.twig', array('form' => $form->createView(), 'action' => 'modification'));
	}
	/**
     * @Route("/admin/client/supp/{id}", name="adminClientSupp")
     */
	public function suppClient($id, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine) // Affichage du formulaire de suppression d'un client
    {
        // On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		
		// Récupération du client d'identifiant $id
		$this->doctrine=$doctrine;
		$rep = $doctrine->getRepository(Client::class);
		$client = $rep->find($id);
		
		// Création du formulaire à partir du client récupéré
		$form = $this->createForm(ClientCompletType::class, $client);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			
			// mise à jour de la bdd
			$res = $rep->suppClient($id);
			$doctrine->getManager()->persist($client);
			$doctrine->getManager()->flush();
				
			// Réaffichage de la liste des clients
			$lesClients = $rep->listeClients();
			$lesClientsPagines = $paginator->paginate(
				$lesClients, // Requête contenant les données à paginer (ici nos clients)
				$request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
				4 // Nombre de résultats par page
			);
			return $this->render('Admin/client.html.twig', Array('lesClients' => $lesClientsPagines));
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('Admin/formClient.html.twig', array('form' => $form->createView(), 'action' => 'suppression'));
    }

	#[Route('/admin/formation/liste', name: 'adminFormationListe')]
	// Gestion des formations
	public function listeFormation(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine)
	{
		// On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		
		$em=$doctrine->getManager();
		$rep = $doctrine->getRepository(Formation::class);
		$lesFormations = $rep->findAll();
		
		$lesFormationsPagines = $paginator->paginate(
            $lesFormations, // Requête contenant les données à paginer (ici nos formations)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            4 // Nombre de résultats par page
        );
		
		return $this->render('Admin/formation.html.twig', Array('lesFormations' => $lesFormationsPagines));
	}
	#[Route('/admin/formation/modif/{id}', name: 'adminFormationModif')]
	// Affichage du formulaire de modification d'une formation
	public function modifFormation($id, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine) // Affichage du formulaire de modification d'un client
    {
		// On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		
		$em=$doctrine->getManager();
		$rep = $em->getRepository(Formation::class);
		$formation = $rep->find($id);
		
		
		// Création du formulaire à partir de la formation récupérée
		$form = $this->createForm(FormationType::class, $formation);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			if ($form->isValid())
			{
				// mise à jour de la bdd
				$em->persist($formation);
				$em->flush();
				
				// Réaffichage de la liste des formations
				$lesFormations = $rep->listeFormations();
				$lesFormationsPagines = $paginator->paginate(
					$lesFormations, // Requête contenant les données à paginer (ici nos formations)
					$request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
					4 // Nombre de résultats par page
				);
				return $this->render('Admin/formation.html.twig', Array('lesFormations' => $lesFormationsPagines));
			}
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('Admin/formFormation.html.twig', array('form' => $form->createView(), 'action' => 'modification'));
	}
	#[Route('/admin/formation/supp/{id}', name: 'adminFormationSupp')]
	// Suppression d'une formation
	public function suppFormation($id, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine) // Affichage du formulaire de suppression d'une formation
    {
        // On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		
		// Récupération de la formation d'identifiant $id
		$em=$doctrine->getManager();
		$rep = $doctrine->getRepository(Formation::class);
		$formation = $rep->find($id);
		
		// Création du formulaire à partir de la formation récupérée
		$form = $this->createForm(FormationType::class, $formation);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			
			// mise à jour de la bdd
			$res = $rep->suppFormation($id);
			$em->persist($formation);
			$em->flush();
				
			// Réaffichage de la liste des formations
			$lesFormations = $rep->listeFormations();
			$lesFormationsPagines = $paginator->paginate(
				$lesFormations, // Requête contenant les données à paginer (ici nos formations)
				$request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
				4 // Nombre de résultats par page
			);
			return $this->render('Admin/formation.html.twig', Array('lesFormations' => $lesFormationsPagines));
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('Admin/formFormation.html.twig', array('form' => $form->createView(), 'action' => 'suppression'));
    }

	#[Route('/admin/session/liste', name: 'adminSessionListe')]
	// Gestion des sessions de formation
	public function listeSession(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine)
	{
		// On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		
		$em=$doctrine->getManager();
		$rep = $em->getRepository(SessionFormation::class);
		$lesSessions = $rep->findAll();
		
		$lesSessionsPagines = $paginator->paginate(
            $lesSessions, // Requête contenant les données à paginer (ici nos sessions de formation)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            4 // Nombre de résultats par page
        );
		
		return $this->render('Admin/session.html.twig', Array('lesSessions' => $lesSessionsPagines));
	}
	#[Route('/admin/session/modif/{id}', name: 'adminSessionModif')]
	// Affichage du formulaire de modification d'une session de formation
	public function modifSession($id, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine) // Affichage du formulaire de modification d'une session de formation
    {
        // On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		
		// Récupération de la session de formation d'identifiant $id
		$em=$doctrine->getManager();
		$rep = $em->getRepository(SessionFormation::class);
		$session = $rep->find($id);
		
		// Création du formulaire à partir de la session de formation récupérée
		$form = $this->createForm(SessionType::class, $session);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			if ($form->isValid())
			{
				// mise à jour de la bdd
				$em->persist($session);
				$em->flush();
				
				// Réaffichage de la liste des sessions de formation
				$lesSessions = $rep->listeSessions();
				$lesSessionsPagines = $paginator->paginate(
					$lesSessions, // Requête contenant les données à paginer (ici nos sessions de formation)
					$request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
					4 // Nombre de résultats par page
				);
				return $this->render('Admin/session.html.twig', Array('lesSessions' => $lesSessionsPagines));
			}
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('Admin/formSession.html.twig', array('form' => $form->createView(), 'action' => 'modification'));
	}
	#[Route('/admin/session/supp/{id}', name: 'adminSessionSupp')]
	public function suppSession($id, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine) // Affichage du formulaire de suppression d'une session de formation
    {
        // On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		
		// Récupération de la formation d'identifiant $id
		$em=$doctrine->getManager();
		$rep = $em->getRepository(SessionFormation::class);
		$session = $rep->find($id);
		
		// Création du formulaire à partir de la session de formation récupérée
		$form = $this->createForm(SessionType::class, $session);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			
			// mise à jour de la bdd
			$res = $rep->suppSession($id);
			$em->persist($session);
			$em->flush();
				
			// Réaffichage de la liste des sessions de formation
			$lesSessions = $rep->listeSessions();
			$lesSessionsPagines = $paginator->paginate(
				$lesSessions, // Requête contenant les données à paginer (ici nos sessions de formation)
				$request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
				4 // Nombre de résultats par page
			);
			return $this->render('Admin/session.html.twig', Array('lesSessions' => $lesSessionsPagines));
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('Admin/formSession.html.twig', array('form' => $form->createView(), 'action' => 'suppression'));
    }

	#[Route('/admin/plan/liste', name: 'adminPlanListe')]
	// Gestion des plans de formation
	public function listePlanFormation(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine)
	{
		// On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		
		$em=$doctrine->getManager();
		$rep = $em->getRepository(PlanFormation::class);
		$lesPlans = $rep->findAll();
		
		$lesPlansPagines = $paginator->paginate(
            $lesPlans, // Requête contenant les données à paginer (ici nos plans de formation)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            4 // Nombre de résultats par page
        );
		
		return $this->render('Admin/plan.html.twig', Array('lesPlans' => $lesPlansPagines));
	}
	#[Route('/admin/plan/modif/{id}', name: 'adminPlanModif')]
	// Affichage du formulaire de modification d'un plan de formation
	public function modifPlanFormation($id, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine) // Affichage du formulaire de modification d'un plan de formation
    {
        // On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		
		// Récupération du plan de formation d'identifiant $id
		$em=$doctrine->getManager();
		$rep = $em->getRepository(PlanFormation::class);
		$plan = $rep->find($id);
		
		// Création du formulaire à partir du plan de formation récupéré
		$form = $this->createForm(PlanFormationType::class, $plan);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			if ($form->isValid())
			{
				// mise à jour de la bdd
				$em->persist($plan);
				$em->flush();
				
				// Réaffichage de la liste des plans de formation
				$lesPlans = $rep->listePlans();
				$lesPlansPagines = $paginator->paginate(
					$lesPlans, // Requête contenant les données à paginer (ici nos plans de formation)
					$request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
					4 // Nombre de résultats par page
				);
				return $this->render('Admin/plan.html.twig', Array('lesPlans' => $lesPlansPagines));
			}
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('Admin/formPlan.html.twig', array('form' => $form->createView(), 'action' => 'modification'));
	}
	/**
     * @Route("/admin/plan/supp/{id}", name="adminPlanSupp")
     */
	#[Route('/admin/plan/supp/{id}', name: 'adminPlanSupp')]
	public function suppPlanFormation($id, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine) // Affichage du formulaire de suppression d'un plan de formation
    {
        // On autorise l'acces uniquement à l'administrateur
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		
		// Récupération du plan de formation d'identifiant $id
		$em=$doctrine->getManager();
		$rep = $em->getRepository(PlanFormation::class);
		$plan = $rep->find($id);
		
		// Création du formulaire à partir du plan de formation récupéré
		$form = $this->createForm(PlanFormationType::class, $plan);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			
			// mise à jour de la bdd
			$res = $rep->suppPlan($id);
			$em->persist($plan);
			$em->flush();
				
			// Réaffichage de la liste des plans de formation
			$lesPlans = $rep->listePlans();
			$lesPlansPagines = $paginator->paginate(
				$lesPlans, // Requête contenant les données à paginer (ici nos plans de formation)
				$request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
				4 // Nombre de résultats par page
			);
			return $this->render('Admin/plan.html.twig', Array('lesPlans' => $lesPlansPagines));
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('Admin/formPlan.html.twig', array('form' => $form->createView(), 'action' => 'suppression'));
    }
}
