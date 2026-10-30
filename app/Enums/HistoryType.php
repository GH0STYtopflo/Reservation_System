<?php

namespace App\Enums;

enum HistoryType: string
{
    case CONDITION = 'condition';
    case MEDICATION = 'medication';
    case SURGERY = 'surgery';
}
