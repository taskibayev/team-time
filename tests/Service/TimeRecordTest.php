<?php

namespace App\Tests\Service;

use App\Entity\Employee;
use App\Entity\TimeRecord;
use PHPUnit\Framework\TestCase;

final class TimeRecordTest extends TestCase
{
    public function testStopPersistsTimestampAndRestartIsSeparateRecord(): void
    {
        $e = new Employee();
        $start = new \DateTimeImmutable('2026-01-01T10:00:00+05:00');
        $first = new TimeRecord($e, 'Work', $start);
        $first->stop($start->modify('+1 hour'));
        $second = new TimeRecord($e, 'Work again', $start->modify('+2 hours'));
        self::assertSame(3600, $first->getEndedAt()?->getTimestamp() - $start->getTimestamp());
        self::assertTrue($second->isRunning());
        self::assertNotSame($first, $second);
    }
}
