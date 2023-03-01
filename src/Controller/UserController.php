<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/user/new', name: 'user_new')]
    public function new(Request $request,UserPasswordHasherInterface $userPasswordHasher, ManagerRegistry $doctrine): Response
    {
        $user = new User($userPasswordHasher);// on instancie un objet User
        $form = $this->createForm(UserType::class, $user);// $form recuperer les données du formulaire et les injecter dans $user
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {// si le formulaire est soumis et valide
            $em = $doctrine->getManager();// on recupere le manager de doctrine
            $em->persist($user);// on enregistre l'objet $user
            $em->flush();// on enregistre en base de données
            return $this->redirectToRoute('home');// on redirige vers la page d'accueil
        }
        return $this->render('user/form.html.twig', [// on affiche le formulaire
            'user_form' => $form->createView(),// on envoie le formulaire à la vue
        ]);
    }
}