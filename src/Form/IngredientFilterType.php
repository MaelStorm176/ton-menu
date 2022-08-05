<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class IngredientFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('ingredients', EntityType::class, [
            'label' => 'Ingredients',
            'required' => false,
            'class' => 'App\Entity\Ingredient',
            'attr' => ['class' => 'form-control w-100'],
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => false,
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Rechercher',
            'attr' => ['class' => 'btn btn-primary']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
