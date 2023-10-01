<?php

namespace App\Enums;

enum TransactionType: int
{
    case WITHDRAW = 0;
    case LOCAL_TRANSFER = 1;
}
