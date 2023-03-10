<?php

namespace App\Controller;

use App\Form\PostType;
use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{
    #[Route('/', name: 'home')] // on definit la route
    public function index(PostRepository $repository, Request $request): Response // on recupere le manager de doctrine
    {
        $search = $request->request->get('search'); // on recupere la valeur de la recherche
        $posts = $repository->findAll(); // on recupere tous les posts
        if($search) { // si la recherche n'est pas vide
            $posts = $repository->findBySearch($search); // on recupere les posts qui correspondent à la recherche
        }
        return $this->render('post/index.html.twig', [ // on affiche la vue
            "posts" => $posts // on envoie les posts à la vue
        ]);
    }

    #[Route('/post/new')]
    public function create(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); // on verifie que l'utilisateur est connecté
        $post = new Post(); // on instancie un objet Post
        $form = $this->createForm(PostType::class, $post);
        // $form recuperer les données du formulaire et les injecter dans $post
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { // si le formulaire est soumis et valide
            $image = $form->get('image')->getData(); // on recupere l'image
            if($image) { // si l'image n'est pas vide
                $orginalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME); // on recupere le nom de l'image
                $safeFilename = $slugger->slug($orginalFilename); // on genere un nouveau nom pour l'image
                $newFilename = $orginalFilename.'-'.uniqid().'.'.$image->guessExtension(); // on genere un nouveau nom pour l'image
                try {
                    $image->move(
                        $this->getParameter('uploads'), // on recupere le chemin du dossier images
                        $newFilename // on enregistre l'image dans le dossier images
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $post->setImage($newFilename); // on enregistre le nom de l'image dans la base de données
            }

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
    public function update(Post $post, ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
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
            $image = $form->get('image')->getData(); // on recupere l'image
            if($image) { // si l'image n'est pas vide
                $orginalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME); // on recupere le nom de l'image
                $safeFilename = $slugger->slug($orginalFilename); // on genere un nouveau nom pour l'image
                $newFilename = $orginalFilename.'-'.uniqid().'.'.$image->guessExtension(); // on genere un nouveau nom pour l'image
                try {
                    $image->move(
                        $this->getParameter('uploads'), // on recupere le chemin du dossier images
                        $newFilename // on enregistre l'image dans le dossier images
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $post->setImage($newFilename); // on enregistre le nom de l'image dans la base de données
            }
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
