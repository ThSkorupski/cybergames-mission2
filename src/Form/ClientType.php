<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $creation = $options['creation'];

        $builder
            ->add('email', EmailType::class, ['label' => 'Adresse e-mail', 'empty_data' => ''])
            ->add('nom', TextType::class, ['label' => 'Nom', 'empty_data' => ''])
            ->add('prenom', TextType::class, ['label' => 'Prénom', 'empty_data' => ''])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo (facultatif)',
                'required' => false,
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => $creation ? 'Mot de passe' : 'Nouveau mot de passe (laisser vide pour ne pas le changer)',
                'mapped' => false,
                'required' => $creation,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => array_filter([
                    $creation ? new NotBlank(message: 'Le mot de passe est obligatoire.') : null,
                    new Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.', max: 4096),
                ]),
            ]);

        if (!$creation) {
            $builder->add('actif', CheckboxType::class, [
                'label' => 'Compte actif (décocher pour désactiver)',
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'creation' => false,
        ]);
        $resolver->setAllowedTypes('creation', 'bool');
    }
}
