<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class IngredientFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nb_jours', ChoiceType::class, [
            'choices' => [
                '-- Nombre de jours --' => null,
                '1 jour' => 1,
                '2 jours' => 2,
                '3 jours' => 3,
                '4 jours' => 4,
                '5 jours' => 5,
                '6 jours' => 6,
                '7 jours' => 7,
            ],
            'label' => 'Nombre de jours',
            'required' => false,
        ])
        ->add('ingredients', EntityType::class, [
            'label' => 'Ingredients',
            'required' => false,
            'class' => 'App\Entity\Ingredient',
            'attr' => ['class' => 'form-control w-100'],
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => false,
        ]);
        /*->add('maxDuration', ChoiceType::class, [
            'label' => 'Temps passé en cuisine',
            'required' => false,
            'label_attr' => ['class' => 'checkbox-inline'],
            'choices' => [
                '< 30min' => 3,
                '< 1h' => 1,
                '1h - 2h' => 2,
            ],
        ])*/
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
