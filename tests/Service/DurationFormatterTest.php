<?php

namespace App\Tests\Service;

use App\Service\DurationFormatter;
use PHPUnit\Framework\TestCase;

final class DurationFormatterTest extends TestCase
{
    public function testExactSecondsAreSummedBeforeMinuteTruncation(): void
    {
        $f = new DurationFormatter();
        self::assertSame('0h 01m', $f->format(40 + 40));
    }

    public function testWorkingEquivalents(): void
    {
        $f = new DurationFormatter();
        $v = $f->equivalents(8 * 3600);
        self::assertSame(1.0, $v['days']);
        self::assertSame(0.2, $v['weeks']);
        self::assertSame(0.05, $v['months']);
        self::assertNull($f->workingEquivalent(8 * 3600));
        self::assertSame('1.25 days', $f->workingEquivalent(10 * 3600));
        self::assertSame('1.25 weeks', $f->workingEquivalent(50 * 3600));
        self::assertSame('1.25 months', $f->workingEquivalent(200 * 3600));
    }
}
