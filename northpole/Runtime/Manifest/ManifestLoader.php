<?php

namespace Northpole\Runtime\Manifest;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;

final class ManifestLoader
{
    public function load(string $modulePath): ModuleManifest
    {
        $manifest = $modulePath.DIRECTORY_SEPARATOR.'module.json';

        if (! File::exists($manifest)) {
            throw new InvalidArgumentException(
                "Module manifest not found: {$manifest}"
            );
        }

        $json = json_decode(File::get($manifest), true);

        if (! is_array($json)) {
            throw new InvalidArgumentException(
                "Invalid JSON in {$manifest}"
            );
        }

        return new ModuleManifest(
            $json,
            $modulePath,
            $manifest
        );
    }
}