<?php

namespace App\Enums;

enum TransactionStatus: int
{
    case DRAFT = 0;
    case SUCCESS = 1;
    case FAILED = 2;
    case INSUFFICIENT_BALANCE = 3;
}
