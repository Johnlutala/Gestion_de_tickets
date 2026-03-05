<?php

namespace App\Form;

use App\Entity\Privilege;
use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PrivilegeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ du nom du privilège
            ->add('name', TextType::class, [
                'label' => 'Nom du privilège',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom du privilège',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Le nom du privilège est obligatoire'
                    ),
                ],
            ])
            // Champ de description
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez une description',
                    'rows' => 3,
                ],
            ])
            // Champ roles lié à l'entité Role
            ->add('roles', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'nom', // afficher le nom du rôle
                'label' => 'Rôles associés',
                'multiple' => true, // permettre de sélectionner plusieurs rôles
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
            'data_class' => Privilege::class,
        ]);
    }
}
