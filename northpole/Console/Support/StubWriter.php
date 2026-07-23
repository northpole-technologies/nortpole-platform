<?php

declare(strict_types=1);

namespace Northpole\Console\Support;

use RuntimeException;

final class StubWriter
{
    /**
     * Write a stub file to a destination after replacing its placeholders.
     *
     * @param array<string, string> $replacements
     */
    public function write(
        string $stubPath,
        string $destinationPath,
        array $replacements = [],
        bool $overwrite = false,
    ): void {
        $this->ensureStubExists($stubPath);
        $this->ensureDestinationCanBeWritten(
            destinationPath: $destinationPath,
            overwrite: $overwrite,
        );

        $contents = file_get_contents($stubPath);

        if ($contents === false) {
            throw new RuntimeException(
                sprintf('Unable to read stub file [%s].', $stubPath),
            );
        }

        $renderedContents = $this->replacePlaceholders(
            contents: $contents,
            replacements: $replacements,
        );

        $this->ensureDestinationDirectoryExists($destinationPath);

        $bytesWritten = file_put_contents(
            filename: $destinationPath,
            data: $renderedContents,
        );

        if ($bytesWritten === false) {
            throw new RuntimeException(
                sprintf(
                    'Unable to write generated file [%s].',
                    $destinationPath,
                ),
            );
        }
    }

    private function ensureStubExists(string $stubPath): void
    {
        if (! is_file($stubPath)) {
            throw new RuntimeException(
                sprintf('Stub file [%s] does not exist.', $stubPath),
            );
        }

        if (! is_readable($stubPath)) {
            throw new RuntimeException(
                sprintf('Stub file [%s] is not readable.', $stubPath),
            );
        }
    }

    private function ensureDestinationCanBeWritten(
        string $destinationPath,
        bool $overwrite,
    ): void {
        if (is_file($destinationPath) && ! $overwrite) {
            throw new RuntimeException(
                sprintf(
                    'Destination file [%s] already exists.',
                    $destinationPath,
                ),
            );
        }
    }

    private function ensureDestinationDirectoryExists(
        string $destinationPath,
    ): void {
        $directory = dirname($destinationPath);

        if (is_dir($directory)) {
            return;
        }

        $created = mkdir(
            directory: $directory,
            permissions: 0755,
            recursive: true,
        );

        if (! $created && ! is_dir($directory)) {
            throw new RuntimeException(
                sprintf(
                    'Unable to create destination directory [%s].',
                    $directory,
                ),
            );
        }
    }

    /**
     * @param array<string, string> $replacements
     */
    private function replacePlaceholders(
        string $contents,
        array $replacements,
    ): string {
        foreach ($replacements as $placeholder => $replacement) {
            $contents = str_replace(
                search: '{{'.$placeholder.'}}',
                replace: $replacement,
                subject: $contents,
            );
        }

        return $contents;
    }
}