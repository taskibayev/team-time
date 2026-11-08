<?php

namespace App\Service;

use App\Entity\Employee;
use App\Entity\TimeRecord;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;

final class ReportService
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly DurationFormatter $formatter)
    {
    }

    /**
     * @param list<int>    $employeeIds
     * @param list<string> $positions
     * @param list<string> $departments
     *
     * @return array{rows:list<array{employee:Employee,seconds:int,equivalent:?string}>,details:list<array{record:TimeRecord,seconds:int}>,total:int}
     */
    public function generate(\DateTimeImmutable $from, \DateTimeImmutable $to, \DateTimeImmutable $now, array $employeeIds = [], array $positions = [], array $departments = []): array
    {
        $query = $this->em->getRepository(TimeRecord::class)->createQueryBuilder('r')->join('r.employee', 'e')->addSelect('e')->andWhere('r.startedAt < :to')->andWhere('r.endedAt IS NULL OR r.endedAt > :from')->setParameter('from', $from, Types::DATETIMETZ_IMMUTABLE)->setParameter('to', $to, Types::DATETIMETZ_IMMUTABLE)->orderBy('r.startedAt', 'DESC');
        if ($employeeIds) {
            $query->andWhere('e.id IN (:ids)')->setParameter('ids', $employeeIds);
        }

        if ($positions) {
            $query->andWhere('e.position IN (:positions)')->setParameter('positions', $positions);
        }

        if ($departments) {
            $query->andWhere('e.department IN (:departments)')->setParameter('departments', $departments);
        }

        /** @var array<int,int> $totals */ $totals = [];
        /** @var list<array{record:TimeRecord,seconds:int}> $details */ $details = [];
        /** @var array<int,Employee> $employees */ $employees = [];
        foreach ($query->getQuery()->getResult() as $record) {
            $start = max($from->getTimestamp(), $record->getStartedAt()->getTimestamp());
            $end = min($to->getTimestamp(), ($record->getEndedAt() ?? $now)->getTimestamp());
            $seconds = max(0, $end - $start);
            $id = $record->getEmployee()->getId();
            if (null === $id) {
                continue;
            }

            $totals[$id] = ($totals[$id] ?? 0) + $seconds;
            $employees[$id] = $record->getEmployee();
            $details[] = ['record' => $record, 'seconds' => $seconds];
        }

        $rows = [];
        foreach ($totals as $id => $seconds) {
            $rows[] = ['employee' => $employees[$id], 'seconds' => $seconds, 'equivalent' => $this->formatter->workingEquivalent($seconds)];
        }

        return ['rows' => $rows, 'details' => 1 === count($employeeIds) ? $details : [], 'total' => (int) array_sum($totals)];
    }
}
