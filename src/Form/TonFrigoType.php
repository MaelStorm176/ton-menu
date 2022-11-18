<?php

namespace App\Form;

use App\Entity\Recipe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TonFrigoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ingredients', EntityType::class, [
                'label' => 'Ingredients dans votre frigo',
                'required' => true,
                'class' => 'App\Entity\Ingredient',
                'attr' => ['class' => 'form-control w-100'],
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
            ])
            ->add('ingredients_not', EntityType::class, [
                'label' => 'Ingredients à bannir',
                'required' => false,
                'class' => 'App\Entity\Ingredient',
                'attr' => ['class' => 'form-control w-100'],
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
            ])
            ->add('maxDuration', ChoiceType::class, [
                'label' => 'Avez-vous le temps de cuisiner ?',
                'required' => false,
                'label_attr' => ['class' => 'checkbox-inline'],
                'choices' => [
                    'Aucune importance' => null,
                    '< 30min' => 3,
                    '< 1h' => 1,
                    '1h - 2h' => 2,
                ],
            ])
            ->add('number_of_persons', IntegerType::class, [
                'label' => 'Combien êtes-vous ?',
                'required' => false,
                'attr' => ['maxlength' => 4, 'min' => 1, 'max' => 100, 'step' => 1, 'class' => 'form-control', 'placeholder' => 'Nombre de personnes']
            ])
            ->add('menu_complet', CheckboxType::class, [
                'label' => 'Souhaitez-vous un menu complet ? (entrée, plat, dessert)',
                'required' => false,
                'label_attr' => ['class' => 'checkbox-inline'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Proposez moi un menu !',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    /*
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
    */
}