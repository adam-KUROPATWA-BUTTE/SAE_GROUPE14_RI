<?php

namespace Tests\Model\Entity;

use PHPUnit\Framework\TestCase;
use Model\Entity\FolderStats;

class FolderStatsTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $stats = new FolderStats(10, 7);

        $this->assertEquals(10, $stats->getTotal());
        $this->assertEquals(7, $stats->getCompleted());
    }

    public function testCompletionPercentage(): void
    {
        $stats = new FolderStats(10, 5);

        $this->assertEquals(50.0, $stats->getCompletionPercentage());
    }

    public function testCompletionPercentageWithZeroTotal(): void
    {
        $stats = new FolderStats(0, 0);

        $this->assertEquals(0, $stats->getCompletionPercentage());
    }
}