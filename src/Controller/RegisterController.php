<?php
namespace App\Controller;

use App\Entity\Client;
use App\Entity\Statut;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passEncoder, ManagerRegistry $doctrine)
    {
        // Création du formulaire
        $form = $this->createFormBuilder()
            ->add('username', TextType::class)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmation Mot de passe'],
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'ROLE_USER' => 'ROLE_USER',
                    'ROLE_ADMIN' => 'ROLE_ADMIN',
                    'ROLE_CLIENT' => 'ROLE_CLIENT',
                ],
                'multiple' => true
            ])
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('statut', EntityType::class, [
                'class' => Statut::class,
                'choice_label' => 'type',
                'multiple' => false
            ])
            ->add('adresse', TextType::class)
            ->add('cp', TextType::class)
            ->add('ville', TextType::class)
            ->add('email', TextType::class)
            ->add('nbhcompta', NumberType::class)
            ->add('nbhbur', NumberType::class)
            ->add('tel', TextType::class)
            ->add('enregistrer', SubmitType::class, [
                'attr' => ['class' => 'btn btn-success']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $client = new Client();
            $client->setUsername($data['username']);
            $client->setPassword($passEncoder->hashPassword($client, $data['password']));
            $client->setRoles($data['roles']);
            $client->setNom($data['nom']);
            $client->setPrenom($data['prenom']);
            $client->setStatut($data['statut']);
            $client->setAdresse($data['adresse']);
            $client->setCp($data['cp']);
            $client->setVille($data['ville']);
            $client->setEmail($data['email']);
            $client->setNbhcompta($data['nbhcompta']);
            $client->setNbhbur($data['nbhbur']);
            $client->setTel($data['tel']);

            $em = $doctrine->getManager();
            $em->persist($client);
            $em->flush();

            return $this->redirectToRoute('clients_list'); // redirige vers la liste des clients
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}