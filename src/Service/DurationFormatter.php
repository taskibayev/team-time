<?php

namespace App\Service;

final class DurationFormatter
{
    public function format(int $s): string
    {
        $m = intdiv(max(0, $s), 60);

        return sprintf('%dh %02dm', intdiv($m, 60), $m % 60);
    }

    /** @return array{days:float,weeks:float,months:float} */
    public function equivalents(int $s): array
    {
        $h = max(0, $s) / 3600;

        return ['days' => round($h / 8, 2), 'weeks' => round($h / 40, 2), 'months' => round($h / 160, 2)];
    }

    public function workingEquivalent(int $seconds): ?string
    {
        $hours = max(0, $seconds) / 3600;
        if ($hours > 160) {
            return $this->decimal($hours / 160) . ' months';
        }

        if ($hours > 40) {
            return $this->decimal($hours / 40) . ' weeks';
        }

        if ($hours > 8) {
            return $this->decimal($hours / 8) . ' days';
        }

        return null;
    }

    private function decimal(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
