<?php

namespace App\Tests\Integration;

use App\Entity\TimeRecord;
use App\Service\ReportService;

final class ReportServiceTest extends DatabaseTestCase
{
    public function testReportClipsRecordsToPeriodAndAppliesEmployeeFilter(): void
    {
        $employee = $this->createEmployee();
        $timezone = new \DateTimeZone('Asia/Aqtau');
        $record = new TimeRecord($employee, 'Integration report task', new \DateTimeImmutable('2026-11-08 09:00:00', $timezone));
        $record->stop(new \DateTimeImmutable('2026-11-08 12:00:00', $timezone));
        $this->entityManager->persist($record);
        $this->entityManager->flush();

        $employeeId = $employee->getId();
        self::assertNotNull($employeeId);
        $report = self::getContainer()->get(ReportService::class)->generate(
            new \DateTimeImmutable('2026-11-08 10:00:00', $timezone),
            new \DateTimeImmutable('2026-11-08 11:00:00', $timezone),
            new \DateTimeImmutable('2026-11-08 13:00:00', $timezone),
            [$employeeId],
        );

        self::assertSame(3600, $report['total']);
        self::assertCount(1, $report['rows']);
        self::assertCount(1, $report['details']);
        self::assertSame('Integration report task', $report['details'][0]['record']->getDescription());
    }
}
