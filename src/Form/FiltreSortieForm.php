<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreSortieForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'placeholder' => '-- Tous les campus --',
                'required' => false,
                'label' => 'Campus',
            ])
            ->add('search', TextType::class, [
                'required' => false,
                'label' => 'Nom de la sortie',
            ])
            ->add('dateDebut', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Entre',
            ])
            ->add('dateFin', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Et',
            ])
            ->add('inscrit', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties auxquelles je suis inscrit/e",
            ])
            ->add('non_inscrit', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties auxquelles je ne suis pas inscrit/e",
            ])
            ->add('organisateur', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties dont je suis l'organisateur/trice"
            ])
            ->add('terminees', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties terminées",
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'campus_choices' => [],
        ]);
    }
}
