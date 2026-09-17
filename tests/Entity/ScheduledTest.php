<?php

namespace App\Tests\Entity;

use App\Entity\Scheduled;
use PHPUnit\Framework\TestCase;

class ScheduledTest extends TestCase
{
    public function testIsWithinDateRangeWithValidDates(): void
    {
        $scheduled = new Scheduled();
        $scheduled->setBeginAt(new \DateTimeImmutable('2026-09-15'));
        $scheduled->setEndAt(new \DateTimeImmutable('2026-09-30'));

        $validDate = new \DateTimeImmutable('2026-09-17');
        $this->assertTrue($scheduled->isWithinDateRange($validDate));

        $beginDate = new \DateTimeImmutable('2026-09-15');
        $this->assertTrue($scheduled->isWithinDateRange($beginDate));

        $endDate = new \DateTimeImmutable('2026-09-30');
        $this->assertTrue($scheduled->isWithinDateRange($endDate));
    }

    public function testIsWithinDateRangeWithInvalidDates(): void
    {
        $scheduled = new Scheduled();
        $scheduled->setBeginAt(new \DateTimeImmutable('2026-09-15'));
        $scheduled->setEndAt(new \DateTimeImmutable('2026-09-30'));

        $beforeDate = new \DateTimeImmutable('2026-09-01');
        $this->assertFalse($scheduled->isWithinDateRange($beforeDate));

        $afterDate = new \DateTimeImmutable('2026-10-01');
        $this->assertFalse($scheduled->isWithinDateRange($afterDate));
    }

    public function testIsWithinDateRangeDefaultCurrentDate(): void
    {
        $today = new \DateTimeImmutable();
        $scheduledValid = new Scheduled();
        $scheduledValid->setBeginAt($today->modify('-1 day'));
        $scheduledValid->setEndAt($today->modify('+1 day'));

        $this->assertTrue($scheduledValid->isWithinDateRange());

        $scheduledExpired = new Scheduled();
        $scheduledExpired->setBeginAt($today->modify('-10 days'));
        $scheduledExpired->setEndAt($today->modify('-5 days'));

        $this->assertFalse($scheduledExpired->isWithinDateRange());
    }
}
