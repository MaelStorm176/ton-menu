<?php

namespace App\Form;

use App\Entity\Recipe;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('preparation_time', IntegerType::class,[
                'label' => 'Temps de préparation',
                'attr' => ['maxlength' => 4, 'min' => 0]
            ])
            ->add('total_time',IntegerType::class,[
                'label' => 'Temps total pour effectuer la recette',
                'attr' => ['maxlength' => 4, 'min' => 0]
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
