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
            ->add('description', TextType::class,[
                'label' => 'Note de l\'auteur',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ajoutez une note / citation à votre recette (100 car. max)',
                    'maxlength' => '100',
                ]
            ])
            ->add('url', UrlType::class,[
                'label' => 'URL',
                'attr' => ['placeholder' => 'Lien vers la source de la recette'],
                'required' => false
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
                'required' => true,
            ])
            ->add('budget', ChoiceType::class,[
                'label' => 'Budget',
                'choices'  => [
                    '-- Selectionnez un budget --' => null,
                    'Cheap' => 1,
                    'Moyen' => 2,
                    'Chère' => 3,
                ],
                'required' => true,
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
            ->add('recipeSteps', CollectionType::class,[
                'entry_type' => StepType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('recipeQuantities', CollectionType::class,[
                'entry_type' => QuantityType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('recipeTags', EntityType::class, [
                'label' => 'Tags',
                'required' => false,
                'class' => 'App\Entity\RecipeTags',
                'attr' => ['class' => 'form-control w-100'],
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
            ])
            ->add('ingredients', EntityType::class, [
                'label' => 'Ingredients',
                'required' => true,
                'class' => 'App\Entity\Ingredient',
                'attr' => ['class' => 'form-control w-100'],
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
            ])
            ->add('Enregistrer', SubmitType::class)
        ;
    }

/* ->add('recipeImages', FileType::class,[
                'label' => 'Images de la recette',
                'multiple' => true,
                'required' => false,
                'attr' => ['placeholder' => 'Images de la recette']
            ])
*/


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
            'attr' => [
                'enctype' => 'multipart/form-data'
            ]
        ]);
    }
}
