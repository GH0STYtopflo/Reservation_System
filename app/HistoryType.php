<?php

namespace App;

enum HistoryType: string
{
    case CONDITION = 'condition';
    case MEDICATION = 'medication';
    case SURGERY = 'surgery';
}
