<?php

namespace App\Tests\Functional;

use App\Entity\Employee;
use App\Entity\TimeRecord;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TeamTimeWorkflowTest extends WebTestCase
{
    /** @var list<int> */
    private array $employeeIds = [];

    public function testMainPagesAreAvailable(): void
    {
        $client = self::createClient();

        $client->request('GET', '/timers');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Timers');

        $client->request('GET', '/reports');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Reports');
    }

    public function testEmployeeCanBeCreatedThroughForm(): void
    {
        $client = self::createClient();
        $suffix = bin2hex(random_bytes(6));
        $crawler = $client->request('GET', '/timers');
        $form = $crawler->filter('form[action="/timers/employees/add"]')->form([
            'employee[firstName]' => 'Functional',
            'employee[lastName]' => $suffix,
            'employee[position]' => 'engineer',
            'employee[department]' => 'engineering',
        ]);

        $client->submit($form);
        self::assertResponseRedirects('/timers');
        $client->followRedirect();
        self::assertSelectorTextContains('.notice', 'Employee added.');
        self::assertSelectorTextContains('.employee-list', 'Functional ' . $suffix);

        $employee = self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Employee::class)
            ->findOneBy(['lastName' => $suffix]);
        self::assertInstanceOf(Employee::class, $employee);
        $this->rememberEmployee($employee);
    }

    public function testTimerCanBeStartedAndStoppedThroughForms(): void
    {
        $client = self::createClient();
        $employee = $this->createEmployee();
        $employeeId = $employee->getId();
        self::assertNotNull($employeeId);

        $crawler = $client->request('GET', '/timers');
        $startForm = $crawler->filter(sprintf('form[action="/timers/%d/start"]', $employeeId))->form([
            'description' => 'Functional timer task',
        ]);
        $client->submit($startForm);
        self::assertResponseRedirects('/timers');

        $crawler = $client->followRedirect();
        self::assertSelectorTextContains('.running-description', 'Functional timer task');
        $record = self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(TimeRecord::class)
            ->findOneBy(['employee' => $employeeId, 'endedAt' => null]);
        self::assertInstanceOf(TimeRecord::class, $record);
        $recordId = $record->getId();
        self::assertNotNull($recordId);

        $stopForm = $crawler->filter(sprintf('form[action="/timers/record/%d/stop"]', $recordId))->form();
        $client->submit($stopForm);
        self::assertResponseRedirects('/timers');
        $client->followRedirect();
        self::assertSelectorNotExists('.running-description');

        $stopped = self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(TimeRecord::class)
            ->find($recordId);
        self::assertInstanceOf(TimeRecord::class, $stopped);
        self::assertFalse($stopped->isRunning());
    }

    private function createEmployee(): Employee
    {
        $suffix = bin2hex(random_bytes(6));
        $employee = (new Employee())
            ->setFirstName('Functional')
            ->setLastName($suffix)
            ->setPosition('engineer')
            ->setDepartment('engineering');
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($employee);
        $entityManager->flush();
        $this->rememberEmployee($employee);

        return $employee;
    }

    private function rememberEmployee(Employee $employee): void
    {
        $id = $employee->getId();
        self::assertNotNull($id);
        $this->employeeIds[] = $id;
    }

    protected function tearDown(): void
    {
        if (self::$booted) {
            $connection = self::getContainer()->get(EntityManagerInterface::class)->getConnection();
            foreach ($this->employeeIds as $employeeId) {
                $connection->executeStatement('DELETE FROM time_record WHERE employee_id = ?', [$employeeId]);
                $connection->executeStatement('DELETE FROM employee WHERE id = ?', [$employeeId]);
            }
        }

        parent::tearDown();
    }
}
