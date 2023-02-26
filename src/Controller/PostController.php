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
    #[Route('/')]
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
        dump($form);
        // $form recuperer les données du formulaire et les injecter dans $post
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->persist($post);
            $em->flush();
        }
        return $this->render('post/form.html.twig', [
            'post_form' => $form->createView(),
        ]);
    }
}
