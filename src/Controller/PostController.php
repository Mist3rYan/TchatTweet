<?php

namespace App\Controller;

use App\Form\PostType;
use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $repositery = $doctrine->getRepository(Post::class);
        $posts = $repositery->findAll();
        return $this->render('post/index.html.twig', [
            "posts" => $posts
        ]);
    }

    #[Route('/post/new')]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        // $form recuperer les données du formulaire et les injecter dans $post
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->persist($post);
            $em->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('post/form.html.twig', [
            'post_form' => $form->createView(),
        ]);
    }

    #[Route('/post/edit/{id<\d+>}', name: 'edit-post')] // id<\d+> : id doit être un nombre
    public function update(Post $post, ManagerRegistry $doctrine, Request $request): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('post/form.html.twig', [
            'post_form' => $form->createView(),
        ]);
    }

    #[Route('/post/delete/{id<\d+>}', name: 'delete-post')] // id<\d+> : id doit être un nombre
    public function delete(Post $post, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $em->remove($post);
        $em->flush();
        //redirection vers la page d'accueil
        return $this->redirectToRoute('home');
    }

    #[Route('/post/copy/{id<\d+>}', name: 'copy-post')] // id<\d+> : id doit être un nombre
    public function duplicate(Post $post, ManagerRegistry $doctrine): Response
    {
        $copyPost = clone $post;
        $em = $doctrine->getManager();
        $em->persist($copyPost);
        $em->flush();
        return $this->redirectToRoute('home');
    }
}
