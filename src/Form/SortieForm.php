<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;

use App\Repository\LieuRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class SortieForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
				'label' => 'Nom de la sortie '
			])
            ->add('dateHeureDebut', DateTimeType::class, [
				'label' => 'Date et heure de la sortie',
				'widget' => 'single_text',
				'input' => 'datetime_immutable',
            ])
            ->add('duree', null, [
				'label' => 'Durée (en minutes)'
			])
            ->add('dateLimiteInscription', DateTimeType::class, [
				'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
				'input' => 'datetime_immutable',
            ])
            ->add('nbInscriptionMax', null, [
				'label' => 'Nombre de places'
			])
            ->add('infosSortie', null, [
				'label' => 'Déscritption et infos'
			])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => function (Lieu $lieu) {
					return $lieu->getNom().' à '.$lieu->getVille()->getNom();
				},
				'label' => 'Lieu',
				'choice_value' => 'id',

            ])
            ->add('siteOrganisateur', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
				'label' => 'Campus'
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
						'mimeTypesMessage' => 'Please upload a valid image',
					])
				]
			])
			->add('enregistrer', SubmitType::class, [
				'label' => 'Enregistrer',
			])
			->add('publier', SubmitType::class, [
				'label' => 'Publier',
			])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
