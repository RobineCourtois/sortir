<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UtilisateurForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email');

         // Affiche le champ mot de passe uniquement si on est en création
        if ($options['is_creation']) {
            $builder
                ->add('plainPassword', PasswordType::class, [
                    'mapped' => false,
                    'label' => 'Mot de passe',
                    'required' => true,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Le mot de passe ne peut pas être vide',
                        ]),
						new Regex(
							'/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%^&+=.\-_*])([a-zA-Z0-9@#$%^&+=*.\-_]){3,}$/',
							"Le mot de passe doit contenir au moins: une majuscule, une minuscule, un nombre et un caractère spécial (@#$%^&+=.)"
						)
                    ],
                ]);
        }

        $builder
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('administrateur')
            ->add('actif')
            ->add('pseudo')
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
            'is_creation' => false, // Par défaut : formulaire pour modification
        ]);
    }
}
