<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\DBAL\Types\TimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchRecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la recette',
                'attr' => [
                    'placeholder' => 'Nom de la recette',
                ],
                'required' => false
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de recette',
                'label_attr' => ['class' => 'checkbox-inline'],
                'required' => false,
                'choices' => [
                    'Entrée' => "ENTREE",
                    'Plat' => "PLAT",
                    'Dessert' => "DESSERT",
                ],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('difficulty', ChoiceType::class, [
                'label' => 'Difficulté',
                'label_attr' => ['class' => 'checkbox-inline'],
                'required' => false,
                'choices' => [
                    'Très facile' => 1,
                    'Facile' => 2,
                    'Moyen' => 3,
                    'Difficile' => 4,
                ],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('budget', ChoiceType::class, [
                'label' => 'Budget',
                'label_attr' => ['class' => 'checkbox-inline'],
                'required' => false,
                'choices' => [
                    'Cheap' => 1,
                    'Moyen' => 2,
                    'Chère' => 3,
                ],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('maxRate', IntegerType::class, [
                'label' => 'Note max',
                'required' => false,
                'attr' => ['maxlength' => 1, 'min' => 0, 'max' => 5, 'step' => 1, 'class' => 'form-control', 'placeholder' => 'Note maximale de la recette (0-5)'],
            ])
            ->add('minRate', IntegerType::class, [
                'label' => 'Note min',
                'required' => false,
                'attr' => ['maxlength' => 1, 'min' => 0, 'max' => 5, 'step' => 1, 'class' => 'form-control', 'placeholder' => 'Note minimale de la recette (0-5)']
            ])
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
            ->add('number_of_persons', IntegerType::class, [
                'label' => 'Nombre de personnes',
                'required' => false,
                'attr' => ['maxlength' => 4, 'min' => 1, 'max' => 100, 'step' => 1, 'class' => 'form-control', 'placeholder' => 'Nombre de personnes']
            ])
            ->add('tags', EntityType::class, [
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
                'required' => false,
                'class' => 'App\Entity\Ingredient',
                'attr' => ['class' => 'form-control w-100'],
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
            ])
            ->add('author', EntityType::class, [
                'label' => 'Créateur / Chef',
                'required' => false,
                'class' => 'App\Entity\User',
                'attr' => ['class' => 'form-control w-100'],
                'choice_label' => function (User $user) {
                    return $user->getFirstname() . ' ' . $user->getLastname();
                },
                'query_builder' => function (\App\Repository\UserRepository $repository) {
                    return $repository->createQueryBuilder('u')
                        ->where('u.recipes IS NOT EMPTY')
                        ->orderBy('u.firstname', 'ASC')
                        ->addOrderBy('u.lastname', 'ASC');
                },
                'multiple' => true,
                'expanded' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }
}
