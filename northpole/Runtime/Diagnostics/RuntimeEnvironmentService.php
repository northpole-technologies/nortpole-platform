<?php

declare(strict_types=1);

namespace Northpole\Runtime\Diagnostics;

final class RuntimeEnvironmentService
{
    /**
     * @return array{
     *     environment: string,
     *     laravelVersion: string,
     *     phpVersion: string,
     *     peakMemoryBytes: int,
     *     peakMemory: string
     * }
     */
    public function metadata(): array
    {
        $peakMemoryBytes = memory_get_peak_usage(true);

        return [
            'environment' => (string) app()->environment(),
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
            'peakMemoryBytes' => $peakMemoryBytes,
            'peakMemory' => $this->formatBytes(
                $peakMemoryBytes,
            ),
        ];
    }

    /**
     * @return array{
     *     environment: string,
     *     laravelVersion: string,
     *     phpVersion: string
     * }
     */
    public function dashboardData(): array
    {
        $metadata = $this->metadata();

        return [
            'environment' => $metadata['environment'],
            'laravelVersion' =>
                $metadata['laravelVersion'],
            'phpVersion' => $metadata['phpVersion'],
        ];
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    public function consoleRows(): array
    {
        $metadata = $this->metadata();

        return [
            [
                'Environment',
                $metadata['environment'],
            ],
            [
                'Laravel',
                $metadata['laravelVersion'],
            ],
            [
                'PHP',
                $metadata['phpVersion'],
            ],
            [
                'Peak memory',
                $metadata['peakMemory'],
            ],
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = [
            'B',
            'KB',
            'MB',
            'GB',
        ];

        $value = (float) $bytes;
        $unitIndex = 0;

        while (
            $value >= 1024
            && $unitIndex < count($units) - 1
        ) {
            $value /= 1024;
            $unitIndex++;
        }

        return number_format(
            $value,
            $unitIndex === 0 ? 0 : 2,
        ).' '.$units[$unitIndex];
    }
}