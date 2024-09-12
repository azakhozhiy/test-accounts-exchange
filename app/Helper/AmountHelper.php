<?php

namespace App\Helper;

use Decimal\Decimal;

class AmountHelper
{
    public static function calculateServiceAmount(Decimal $amount, Decimal $percent){
        return $amount->div(100)->mul($percent);
    }
}
