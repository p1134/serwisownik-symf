<?php

namespace App\Controller;

use Traversable;
use App\Entity\Repair;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class RepairCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Repair::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('status', 'Status'),
            DateField::new('dateRepair', 'Data naprawy'),
        ];
    }

    public function configureFilters(\EasyCorp\Bundle\EasyAdminBundle\Config\Filters $filters): \EasyCorp\Bundle\EasyAdminBundle\Config\Filters{
        return $filters
            ->add(ChoiceFilter::new('status', 'Status')
                ->setChoices([
                    'Wszystkie' => null,
                    'Oczekujące' => 'planned',
                    'W trakcie' => 'in_progres',
                    'Zakończone' => 'done'
                ]));
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
