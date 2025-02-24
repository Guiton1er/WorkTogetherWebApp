<?php

namespace App\Form;

use App\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom ne peut pas être vide.']),
                    new Assert\Length(['min' => 2, 'max' => 100, 'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères.']),
                ],
            ])
            ->add('lastname', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom ne peut pas être vide.']),
                    new Assert\Length(['min' => 2, 'max' => 100, 'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.']),
                ],
            ])


            ->add('currentPassword', PasswordType::class, [
                'mapped' => false, // Ce champ n'existe pas dans l'entité
                'required' => true,
                'constraints' => [
                    new UserPassword(['message' => 'Mot de passe incorrect.']),
                ],
                'label' => 'Mot de passe actuel'
            ])
    
            // Permettre la modification du mot de passe avec confirmation
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'mapped' => false,
                'first_options' => ['label' => 'Nouveau mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'constraints' => [
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
                    ]),
                ]
            ])

            // ->add('password', PasswordType::class, [
            //     'required' => $options['required'],
            //     'constraints' => [
            //         new Assert\NotBlank(['message' => 'Le mot de passe est obligatoire.']),
            //         new Assert\Length([
            //             'min' => 6,
            //             'max' => 255,
            //             'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
            //         ]),
            //     ],
            //     'empty_data' => '', // Empêche d'avoir une valeur null
            // ])

            ->add('mailAddress', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => "L'adresse email est obligatoire."]),
                    new Assert\Email(['message' => "L'adresse email '{{ value }}' n'est pas valide."]),
                ],
            ])
            ->add('billingAddress', null, [
                'required' => false,
            ])
            
            ->add('postCode', null, [
                'required' => false,
            ])
            
            ->add('town', null, [
                'required' => false,
            ])
            
            ->add('country', null, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}