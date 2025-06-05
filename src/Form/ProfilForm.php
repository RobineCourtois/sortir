<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfilForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('pseudo')
			->add('plainPassword', RepeatedType::class , [
				'type' => PasswordType::class,
				'first_options'  => ['label' => 'Mot de passe', 'hash_property_path' => 'password'],
				'second_options' => ['label' => 'Confirmer le mot de passe'],
				'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le mot de passe ne peut pas être vide',
                    ]),
                ],
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
				'disabled' => true,
            ])
			->add('image', FileType::class, [
				'mapped' => false,
				'required' => false,
				'constraints' => [
					new Image([
						'maxSize' => '1024k',
						'mimeTypes' => [
							'image/jpeg',
							'image/png',
						],
						'mimeTypesMessage' => 'Merci de choisir une image valide (jpeg ou png)',
					])
				]
			])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
