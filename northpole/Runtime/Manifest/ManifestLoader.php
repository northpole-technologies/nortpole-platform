<?php

namespace Northpole\Runtime\Manifest;

use InvalidArgumentException;
use JsonException;

final class ManifestLoader
{
    public function load(string $modulePath): ModuleManifest
    {
        $manifestPath = $modulePath
            .DIRECTORY_SEPARATOR
            .'module.json';

        if (! is_file($manifestPath)) {
            throw new InvalidArgumentException(
                "Module manifest not found: {$manifestPath}"
            );
        }

        $contents = file_get_contents($manifestPath);

        if ($contents === false) {
            throw new InvalidArgumentException(
                "Unable to read module manifest: {$manifestPath}"
            );
        }

        try {
            $data = json_decode(
                $contents,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(
                "Invalid JSON in module manifest [{$manifestPath}]: "
                .$exception->getMessage(),
                previous: $exception
            );
        }

        if (! is_array($data)) {
            throw new InvalidArgumentException(
                "Module manifest must contain a JSON object: {$manifestPath}"
            );
        }

        return new ModuleManifest(
            $data,
            $modulePath,
            $manifestPath
        );
    }
}
