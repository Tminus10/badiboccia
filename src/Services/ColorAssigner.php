<?php

/** Assigns light, visually distinct pastel colors to teams using golden-angle-spaced hues. */
final class ColorAssigner
{
    private const GOLDEN_ANGLE = 137.508;
    private const SATURATION = 0.62;
    private const LIGHTNESS = 0.72;

    public static function colorForIndex(int $index): string
    {
        $hue = fmod($index * self::GOLDEN_ANGLE, 360);
        return self::hslToHex($hue, self::SATURATION, self::LIGHTNESS);
    }

    private static function hslToHex(float $h, float $s, float $l): string
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        $r = (int) round(($r + $m) * 255);
        $g = (int) round(($g + $m) * 255);
        $b = (int) round(($b + $m) * 255);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
