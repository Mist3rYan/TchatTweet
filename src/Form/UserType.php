<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("username", TextType::class, [
                "label" => "Nom d'utilisateur",
                "required" => true,

                "constraints" => [
                    new Assert\NotBlank(["message" => "Le nom d'utilisateur ne doit pas être vide !"]),
                    new Assert\Length([
                        "min" => 2,
                        "max" => 180,
                        "minMessage" => "Le nom d'utilisateur est trop court !",
                        "maxMessage" => "Le nom d'utilisateur ne doit pas dépasser 180 caractères"
                    ])
                ]
            ])
            ->add('password', RepeatedType::class, [// on ajoute un champ de type RepeatedType qui permet de saisir deux fois le même mot de passe
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe']]);
    }

    public function configureOptions(OptionsResolver $resolver)// on configure les options du formulaire
    {
        $resolver->setDefaults([// on configure les options du formulaire
            'data_class' => User::class,// on indique que le formulaire va gérer un objet de type User
            'csrf_protection' => true,// on active la protection contre les attaques CSRF
            'csrf_field_name' => '_token',// on indique le nom du champ qui contiendra le jeton CSRF
            'csrf_token_id' => 'post_item',// on indique l'identifiant du jeton CSRF
        ]);
    }
}
