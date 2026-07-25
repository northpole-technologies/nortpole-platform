<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime\Repair;

use InvalidArgumentException;
use Northpole\Runtime\Repair\RepairRecommendation;
use Northpole\Runtime\Repair\RepairResult;
use Northpole\Runtime\Validation\ValidationSeverity;
use PHPUnit\Framework\TestCase;

final class RepairResultTest extends TestCase
{
    public function test_it_collects_recommendations(): void
    {
        $result = new RepairResult([
            $this->recommendation(
                'repair.warning',
                ValidationSeverity::Warning,
            ),
        ]);

        $this->assertSame(
            1,
            $result->count(),
        );

        $this->assertFalse(
            $result->isEmpty(),
        );
    }

    public function test_it_orders_errors_before_warnings(): void
    {
        $result = new RepairResult([
            $this->recommendation(
                'repair.warning',
                ValidationSeverity::Warning,
            ),
            $this->recommendation(
                'repair.error',
                ValidationSeverity::Error,
            ),
        ]);

        $recommendations = $result->all();

        $this->assertSame(
            'repair.error',
            $recommendations[0]->code(),
        );

        $this->assertSame(
            'repair.warning',
            $recommendations[1]->code(),
        );
    }

    public function test_a_new_result_is_empty(): void
    {
        $result = new RepairResult;

        $this->assertTrue(
            $result->isEmpty(),
        );

        $this->assertSame(
            [],
            $result->toArray(),
        );
    }

    public function test_it_rejects_non_recommendation_values(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new RepairResult([
            'not-a-recommendation',
        ]);
    }

    private function recommendation(
        string $code,
        ValidationSeverity $severity,
    ): RepairRecommendation {
        return new RepairRecommendation(
            code: $code,
            title: $code,
            description: 'Repair recommendation.',
            severity: $severity,
        );
    }
}
