<?php

namespace App\Form;

use App\Entity\CategorieMachine;
use App\Entity\Jeu;
use App\Entity\Machine;
use App\Enum\StatutMachine;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MachineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom de la machine', 'empty_data' => ''])
            ->add('categorie', EntityType::class, [
                'label' => 'Catégorie',
                'class' => CategorieMachine::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Choisir une catégorie',
            ])
            ->add('statut', EnumType::class, [
                'label' => 'Statut',
                'class' => StatutMachine::class,
                'choice_label' => fn (StatutMachine $statut) => $statut->libelle(),
            ])
            ->add('jeux', EntityType::class, [
                'label' => 'Jeux installés',
                'class' => Jeu::class,
                'choice_label' => 'titre',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Machine::class]);
    }
}
