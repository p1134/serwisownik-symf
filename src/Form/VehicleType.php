<?php

namespace App\Form;

use App\Entity\Vehicle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class VehicleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $today = new \DateTime();

        $builder
            ->add('brand')
            ->add('model')
            ->add('year', IntegerType::class, [
                'attr' =>[
                    'min' => 1900,
                    'max' => $today->format('Y'),
                ]
            ])
            ->add('numberPlate')
            ->add('datePurchase', IntegerType::class, [
                'attr' =>[
                    'min' => 1900,
                    'max' => $today->format('Y'),
                ]
            ])
            ->add('service', DateType::class, [
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicle::class,
        ]);
    }
}
