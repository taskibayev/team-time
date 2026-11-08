<?php

namespace App\Form;

use App\Catalog\DepartmentCatalog;
use App\Catalog\PositionCatalog;
use App\Entity\Employee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<Employee> */
final class EmployeeType extends AbstractType
{
    public function __construct(private readonly PositionCatalog $positions, private readonly DepartmentCatalog $departments)
    {
    }

    public function buildForm(FormBuilderInterface $b, array $o): void
    {
        $b->add('firstName')->add('lastName')->add('position', ChoiceType::class, ['choices' => array_flip($this->positions->all())])->add('department', ChoiceType::class, ['choices' => array_flip($this->departments->all())]);
    }

    public function configureOptions(OptionsResolver $r): void
    {
        $r->setDefaults(['data_class' => Employee::class]);
    }
}
