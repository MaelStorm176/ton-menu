<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimeFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('maxDuration', ChoiceType::class, [
            'label' => 'Temps passé en cuisine',
            'required' => false,
            'label_attr' => ['class' => 'checkbox-inline'],
            'choices' => [
                '< 30min' => 3,
                '< 1h' => 1,
                '1h - 2h' => 2,
            ],
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Rechercher',
            'attr' => ['class' => 'btn btn-primary']
        ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
