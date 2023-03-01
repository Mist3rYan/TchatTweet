<?php

namespace App\Controller;

use App\Form\PostType;
use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Node\Expression\Binary\AddBinary;

class PostController extends AbstractController
{
    #[Route('/', name: 'home')] // on definit la route
    public function index(ManagerRegistry $doctrine): Response // on recupere le manager de doctrine
    {
        $repositery = $doctrine->getRepository(Post::class); // on recupere le repositery de Post
        $posts = $repositery->findAll(); // on recupere tous les posts
        return $this->render('post/index.html.twig', [ // on affiche la vue
            "posts" => $posts // on envoie les posts à la vue
        ]);
    }

    #[Route('/post/new')]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); // on verifie que l'utilisateur est connecté
        $post = new Post(); // on instancie un objet Post
        $form = $this->createForm(PostType::class, $post);
        // $form recuperer les données du formulaire et les injecter dans $post
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { // si le formulaire est soumis et valide
            $post->setUser($this->getUser()); // on recupere l'utilisateur connecté
            $post->setPublishedAt( new \DateTime()); // on recupere la date du jour
            $em = $doctrine->getManager(); // on recupere le manager de doctrine
            $em->persist($post); // on enregistre l'objet $post
            $em->flush(); // on enregistre en base de données
            return $this->redirectToRoute('home'); // on redirige vers la page d'accueil
        }
        return $this->render('post/form.html.twig', [ // on affiche le formulaire
            'post_form' => $form->createView(), // on envoie le formulaire à la vue
        ]);
    }

    #[Route('/post/edit/{id<\d+>}', name: 'edit-post')] // id<\d+> : id doit être un nombre
    public function update(Post $post, ManagerRegistry $doctrine, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); // on verifie que l'utilisateur est connecté
        if($this->getUser() !== $post->getUser()) {// on verifie que l'utilisateur connecté est bien l'auteur du post
            $this->addFlash(
               'danger',
               'Vous ne pouvez pas modifier un post qui ne vous appartient pas !'
            );
            return $this->redirectToRoute('home');// si ce n'est pas le cas on redirige vers la page d'accueil
        }
        $form = $this->createForm(PostType::class, $post); // on recupere le formulaire
        $form->handleRequest($request); // on recupere les données du formulaire
        if ($form->isSubmitted() && $form->isValid()) { // si le formulaire est soumis et valide
            $post->setPublishedAt( new \DateTime()); // on recupere la date du jour
            $em = $doctrine->getManager(); // on recupere le manager de doctrine
            $em->flush(); // on enregistre en base de données
            return $this->redirectToRoute('home'); // on redirige vers la page d'accueil
        }
        return $this->render('post/form.html.twig', [ // on affiche le formulaire
            'post_form' => $form->createView(), // on envoie le formulaire à la vue
        ]);
    }

    #[Route('/post/delete/{id<\d+>}', name: 'delete-post')] // id<\d+> : id doit être un nombre
    public function delete(Post $post, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); // on verifie que l'utilisateur est connecté
        if($this->getUser() !== $post->getUser()) {// on verifie que l'utilisateur connecté est bien l'auteur du post
            $this->addFlash(
               'danger',
               'Vous ne pouvez pas supprimer un post qui ne vous appartient pas !'
            );
            return $this->redirectToRoute('home');// si ce n'est pas le cas on redirige vers la page d'accueil
        }
        $em = $doctrine->getManager();
        $em->remove($post); // on supprime l'objet $post
        $em->flush();
        //redirection vers la page d'accueil
        return $this->redirectToRoute('home');
    }

    #[Route('/post/copy/{id<\d+>}', name: 'copy-post')] // id<\d+> : id doit être un nombre
    public function duplicate(Post $post, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); // on verifie que l'utilisateur est connecté
        if($this->getUser() !== $post->getUser()) {// on verifie que l'utilisateur connecté est bien l'auteur du post
            $this->addFlash(
               'danger',
               'Vous ne pouvez pas copier un post qui ne vous appartient pas !'
            );
            return $this->redirectToRoute('home');// si ce n'est pas le cas on redirige vers la page d'accueil
        }
        $copyPost = clone $post;
        $em = $doctrine->getManager();
        $em->persist($copyPost);
        $em->flush();
        return $this->redirectToRoute('home');
    }
}
