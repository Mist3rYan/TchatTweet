<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", TextType::class, [
                "label" => "Titre",
                "required" => false,
                "constraints" => [
                    new Assert\Length([
                        "min" => 0,
                        "max" => 150,
                        "minMessage" => "Le titre ne doit pas dépasser 150 caractères",
                        "maxMessage" => "Le titre ne doit pas dépasser 150 caractères"
                    ])
                ]
            ])
            ->add("content", TextareaType::class, [
                "label" => "Contenu",
                "required" => true,
                "constraints" => [
                    new Assert\NotBlank(["message" => "Le contenue est obligatoire"]),
                    new Assert\Length([
                        "min" => 5,
                        "max" => 350,
                        "minMessage" => "Le contenue doit faire entre 5 et 350 caractères",
                        "maxMessage" => "Le contenue doit faire entre 5 et 350 caractères"
                    ])
                ]
            ])
            ->add("image", UrlType::class, [
                "label" => "URL de l'image",
                "required" => false,
                "constraints" => [
                    new Assert\Url(["message" => "L'URL doit être celle d'une image valide"])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class
        ]);
    }
}
