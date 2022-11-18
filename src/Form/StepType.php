<?php

namespace App\Form;

use App\Entity\RecipeSteps;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('step',TextType::class,[
                'label' => 'Etape :',
                'attr' => ['placeholder' => 'Description de l\'étape'],
                'required' => true
            ])
            //->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecipeSteps::class,
        ]);
    }
}