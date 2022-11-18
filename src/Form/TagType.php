<?php

namespace App\Form;

use App\Entity\RecipeTags;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du tag',
                'attr' => [
                    'placeholder' => 'Entrez le nom du tag',
                ],
            ])
            ->add('recipe', EntityType::class, [
                'label' => 'Attacher ce tag à une/des recettes',
                'required' => false,
                'class' => 'App\Entity\Recipe',
                'attr' => ['class' => 'form-control w-100'],
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
            ])
            ->add('Enregistrer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecipeTags::class,
        ]);
    }
}
