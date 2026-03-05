<?php

namespace App\Form;

use App\Entity\Privilege;
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
            // Champ de description\n            ->add('description', TextareaType::class, [\n                'label' => 'Description',\n                'attr' => [\n                    'class' => 'form-control',\n                    'placeholder' => 'Entrez une description',\n                    'rows' => 3,\n                ],\n            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Privilege::class,
        ]);
    }
}
