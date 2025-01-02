<?php

namespace App\Form;

use App\Entity\Repair;
use App\Entity\Vehicle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class RepairType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vehicle', EntityType::class, [
                'class' => Vehicle::class,
                'choice_label' => function(Vehicle $vehicle){
                    return $vehicle->getBrand().' '.$vehicle->getModel().' | '.$vehicle->getNumberPlate();
                }
            ])
            ->add('part')
            ->add('price')
            ->add('dateRepair', null, [
                'widget' => 'single_text',
                'data' => new \DateTime('now')
            ])
            ->add('description')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Repair::class,
        ]);
    }
}
