<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\Privilege;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ pour le nom du rôle
            ->add('nom', TextType::class, [
                'label' => 'Nom du rôle',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom du rôle',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Le nom du rôle est obligatoire'
                    ),
                ],
            ])
            // Champ pour les privilèges liés
            ->add('privileges', EntityType::class, [
                'class' => Privilege::class,
                'choice_label' => 'name', // afficher le nom du privilège
                'label' => 'Privilèges associés',
                'multiple' => true, // permet plusieurs sélections
                'expanded' => true, // cases à cocher
                'attr' => [
                    'class' => 'form-check',
                ],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
        ]);
    }
}
