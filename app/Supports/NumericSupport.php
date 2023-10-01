<?php

namespace App\Supports;

class NumericSupport
{
    public static function floatToCents(float $value): int
    {
        return bcmul($value, 100);
    }
}
