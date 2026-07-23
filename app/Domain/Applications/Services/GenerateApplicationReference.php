<?php

namespace App\Domain\Applications\Services;

use App\Domain\Applications\Models\Application;
use Illuminate\Support\Str;

class GenerateApplicationReference
{
    /**
     * Generate an opaque, non-sequential tracking reference for a submitted application.
     * Format: CM-[SERVICE_CODE_OR_CAT]-YYYY-[RANDOM6]-[CHECKSUM]
     * Example: CM-PDC-2026-7K9M4X-Q
     */
    public function execute(Application $application): string
    {
        $application->loadMissing('procedureVersion.service');

        $serviceCode = strtoupper(Str::slug($application->procedureVersion->service->code ?? 'APP', ''));
        $prefix = substr($serviceCode, 0, 4);
        if (strlen($prefix) < 3) {
            $prefix = 'GOV';
        }

        $year = now()->format('Y');

        do {
            $random = strtoupper(Str::random(6));
            // Remove confusing characters
            $random = strtr($random, ['0' => '8', 'O' => '9', 'I' => '7', '1' => '6']);
            $checksum = $this->calculateChecksum("CM-$prefix-$year-$random");
            $reference = "CM-$prefix-$year-$random-$checksum";

            $exists = Application::query()->where('reference', $reference)->exists();
        } while ($exists);

        return $reference;
    }

    private function calculateChecksum(string $input): string
    {
        $hash = crc32($input);
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        return $chars[abs($hash) % strlen($chars)];
    }
}
