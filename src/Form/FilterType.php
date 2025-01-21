<?php

namespace App\Form;

use App\Entity\Repair;
use App\Entity\User;
use App\Entity\Vehicle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateRepair', ChoiceType::class, [
                'required' => false,
                // 'widget' => 'single_text',
                'choices' =>[
                    'Obecny miesiąc' => 'Obecny miesiąc',
                    'Poprzedni miesiąc' => 'Poprzedni miesiąc',
                ]
            ])
            ->add('status', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Oczekujące' => 'planned',
                    'W trakcie' => 'in_progress',
                    'Zakończone' => 'done',
                ]
            ])
            ->add('vehicle', EntityType::class, [
                'class' => Vehicle::class,
                'choice_label' => 'id',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Repair::class,
        ]);
    }
}
