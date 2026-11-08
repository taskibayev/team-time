<?php

namespace App\Service;

use App\Entity\Employee;
use App\Entity\TimeRecord;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

final class TimerService
{
    /** @var \Closure():\DateTimeImmutable */ private \Closure $clock;

    public function __construct(private readonly EntityManagerInterface $em, private readonly Connection $connection)
    {
        $this->clock = static fn (): \DateTimeImmutable => new \DateTimeImmutable('now');
    }

    public function start(Employee $employee, string $description): TimeRecord
    {
        $description = trim($description);
        if ('' === $description) {
            throw new \DomainException('Description is required.');
        }

        if (mb_strlen($description) > 255) {
            throw new \DomainException('Description must be 255 characters or fewer.');
        }

        $this->connection->beginTransaction();
        try {
            $this->connection->executeStatement('SELECT pg_advisory_xact_lock(?)', [$employee->getId()]);
            $active = $this->em->getRepository(TimeRecord::class)->findOneBy(['employee' => $employee, 'endedAt' => null]);
            if ($active instanceof TimeRecord) {
                throw new \DomainException('This employee already has an active timer.');
            }

            $record = new TimeRecord($employee, $description, ($this->clock)());
            $this->em->persist($record);
            $this->em->flush();
            $this->connection->commit();

            return $record;
        } catch (\Throwable $e) {
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }

            throw $e;
        }
    }

    public function stop(TimeRecord $record): void
    {
        $record->stop(($this->clock)());
        $this->em->flush();
    }

    /** @param callable():\DateTimeImmutable $clock */
    public function setClock(callable $clock): void
    {
        $this->clock = $clock(...);
    }
}
