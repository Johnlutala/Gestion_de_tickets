<?php

namespace App\Form;

use App\Entity\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'application',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom de l\'application',
                ],
                'constraints' => [
                    new NotBlank(
                        message :'Le nom de l\'application est obligatoire',
                    ),
                ],
            ])
            ->add('key_id', TextType::class, [
                'label' => 'Clé API (Key ID)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez la clé API',
                ],
                'constraints' => [
                    new NotBlank(
                        message :'La clé API est obligatoire',
                    ),
                ],
            ])

            ->add('secret_key', TextType::class, [
                'label' => 'Clé secrète (Secret Key)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez la clé secrète',
                ],
                'constraints' => [
                    new NotBlank(
                        message :'La clé secrète est obligatoire',
                    ),
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Application::class,
        ]);
    }
}
