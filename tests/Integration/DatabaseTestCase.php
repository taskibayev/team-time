<?php

namespace App\Tests\Integration;

use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class DatabaseTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;

    /** @var list<int> */
    private array $employeeIds = [];

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    protected function createEmployee(string $prefix = 'Integration'): Employee
    {
        $suffix = bin2hex(random_bytes(6));
        $employee = (new Employee())
            ->setFirstName($prefix)
            ->setLastName($suffix)
            ->setPosition('engineer')
            ->setDepartment('engineering');
        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        $id = $employee->getId();
        self::assertNotNull($id);
        $this->employeeIds[] = $id;

        return $employee;
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();
        foreach ($this->employeeIds as $employeeId) {
            $connection->executeStatement('DELETE FROM time_record WHERE employee_id = ?', [$employeeId]);
            $connection->executeStatement('DELETE FROM employee WHERE id = ?', [$employeeId]);
        }

        parent::tearDown();
    }
}
