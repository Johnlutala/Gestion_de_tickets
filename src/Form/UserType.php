<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre nom d\'utilisateur   ',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez entrer votre nom d\'utilisateur'
                    ),
                ],
            ])
            ->add('profile', EntityType::class, [
                'class' => Role::class,
                'label' => 'Rôle',
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez un rôle',
                'mapped' => true,
                'expanded' => false, // menu déroulant
                'multiple' => false, // un seul rôle
                'attr' => [
                    'class' => 'form-select border-primary', // Bootstrap 5
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez sélectionner un profil'
                    ),
                ],
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Entrez votre mot de passe',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Confirmez votre mot de passe',
                    ],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez entrer un mot de passe',
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
