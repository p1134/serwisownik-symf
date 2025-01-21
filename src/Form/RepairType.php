<?php

namespace App\Form;

use App\Entity\Repair;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class RepairType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $builder
            ->add('vehicle', EntityType::class, [
                'class' => Vehicle::class,
                'choice_label' => function(Vehicle $vehicle){
                    return $vehicle->getBrand().' '.$vehicle->getModel().' | '.$vehicle->getNumberPlate();
                },
                'placeholder' => 'Wybierz pojazd',
                'query_builder' => function(EntityRepository $repository) use ($user){
                    return $repository->createQueryBuilder('v')
                    ->where('v.owner = :ownerId')
                    ->setParameter('ownerId', $user);
                }
            ])
            ->add('part')
            ->add('price')
            ->add('dateRepair', null, [
                'widget' => 'single_text',
                'data' => new \DateTime('now'),
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Oczekujące' => 'planned',
                    'W trakcie' => 'in_progress',
                    'Zakończone' => 'done',
                ],
                'placeholder' => 'Wybierz',
            ])
            ->add('description');



            // -----------------------------------------------------------------
        

    }

    public function buildFilterForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $builder
        ->add('vehicleFiler', EntityType::class, [
            'class' => Vehicle::class,
            'choice_label' => function(Vehicle $vehicle){
                return $vehicle->getBrand().' '.$vehicle->getModel().' | '.$vehicle->getNumberPlate();
            },
            'placeholder' => 'Wybierz pojazd',
            'query_builder' => function(EntityRepository $repository) use ($user){
                return $repository->createQueryBuilder('v')
                ->where('v.owner = :ownerId')
                ->setParameter('ownerId', $user);
            }
        ])
            ->add('statusFilter', ChoiceType::class, [
                'choices' => [
                    'Oczekujące' => 'planned',
                    'W trakcie' => 'in_progress',
                    'Zakończone' => 'done',
                ],
                'placeholder' => 'Wybierz status',
            ])
            ->add('dateRepairFilter', ChoiceType::class, [
                'choices' => [
                    'Obecny miesiąc' => 'current_month',
                    'Poprzedni miesiąc' => 'previous_month',
                ],
                'placeholder' => 'Wybierz zakres dat'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Repair::class,
            'user' => null
        ]);
    }
}
