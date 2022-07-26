<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Prénom',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre prénom',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Votre prénom doit faire au moins {{ limit }} caractères',
                        'maxMessage' => 'Votre prénom ne peut pas être plus long que {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Nom',
                ],
                'constraints' => [
                    new Length([
                        'min' => 0,
                        'max' => 50,
                        'minMessage' => 'Votre nom doit faire au moins {{ limit }} caractères',
                        'maxMessage' => 'Votre nom ne peut pas être plus long que {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nouveau mot de passe',
                ],
                'empty_data' => '',
            ])
            ->add('profile_picture', FileType::class, [
                'label' => 'Photo de profil',
                'required' => false,
                'data_class' => null
            ])
            ->add("validate", SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'btn btn-secondary profile-button'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
