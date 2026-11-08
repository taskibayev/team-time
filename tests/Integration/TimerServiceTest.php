<?php

namespace App\Tests\Integration;

use App\Service\TimerService;

final class TimerServiceTest extends DatabaseTestCase
{
    public function testTimerPersistsStartAndStopUsingDatabase(): void
    {
        $employee = $this->createEmployee();
        $service = self::getContainer()->get(TimerService::class);
        $startedAt = new \DateTimeImmutable('2026-11-08T10:00:00+05:00');
        $service->setClock(static fn (): \DateTimeImmutable => $startedAt);
        $record = $service->start($employee, 'Integration timer');

        self::assertNotNull($record->getId());
        self::assertTrue($record->isRunning());

        $endedAt = $startedAt->modify('+90 minutes');
        $service->setClock(static fn (): \DateTimeImmutable => $endedAt);
        $service->stop($record);

        self::assertSame($endedAt->getTimestamp(), $record->getEndedAt()?->getTimestamp());
    }

    public function testSecondRunningTimerIsRejected(): void
    {
        $employee = $this->createEmployee();
        $service = self::getContainer()->get(TimerService::class);
        $service->setClock(static fn (): \DateTimeImmutable => new \DateTimeImmutable('2026-11-08T10:00:00+05:00'));
        $service->start($employee, 'First timer');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('This employee already has an active timer.');
        $service->start($employee, 'Second timer');
    }
}
