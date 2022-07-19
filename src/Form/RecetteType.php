<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\RecipeSteps;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class,[
                'label' => 'Nom de la recette',
                'required' => true
            ])
            ->add('type',ChoiceType::class,[
                'label' => 'Type de recette',
                'required' => true,
                'choices'  => [
                    '-- Selectionnez un type de recette --' => null,
                    'Entrée' => "ENTREE",
                    'Plat' => "PLAT",
                    'Dessert' => "DESSERT",
                ],
            ])
            ->add('url', UrlType::class,[
                'label' => 'URL',
                'attr' => ['placeholder' => 'Lien vers la source de la recette']
            ])
            ->add('number_of_persons',IntegerType::class,[
                'label' => 'Nombre de personnes',
                'required' => true,
                'attr' => ['maxlength' => 4, 'min' => 1]
            ])
            ->add('difficulty',ChoiceType::class,[
                'label' => 'Difficulté',
                'choices'  => [
                    '-- Selectionnez une difficulté --' => null,
                    'Très facile' => 1,
                    'Facile' => 2,
                    'Moyen' => 3,
                    'Difficile' => 4,
                ],
            ])
            ->add('budget', ChoiceType::class,[
                'label' => 'Budget',
                'choices'  => [
                    '-- Selectionnez un budget --' => null,
                    'Cheap' => 1,
                    'Moyen' => 2,
                    'Chère' => 4,
                ],
            ])
            ->add('preparationTime', TimeType::class,[
                'label' => 'Temps de prépapation (HH:MM)',
                'required' => true,
                'input' => 'datetime',
                'widget' => 'choice',
                'html5' => false,
                'attr' => ['placeholder' => 'Temps de prépapation'],
            ])
            ->add('totalTime',TimeType::class,[
                'label' => 'Temps total pour effectuer la recette (HH:MM)',
                'required' => true,
                'input' => 'datetime',
                'widget' => 'choice',
                'html5' => false,
                'attr' => ['placeholder' => 'Temps total pour effectuer la recette']
            ])
            ->add('recipeImages', FileType::class,[
                'label' => 'Images de la recette',
                'multiple' => true,
                'required' => false,
                'attr' => ['placeholder' => 'Images de la recette']
            ])
            ->add('recipeSteps', CollectionType::class,[
                'entry_type' => StepType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('Enregistrer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
