<?php

namespace App\Enums;

enum BloodType: string
{
    case APlus = "A+";
    case ANeg = "A-";
    case BPlus = "B+";
    case BNeg = "B-";
    case ABPlus = "AB+";
    case ABNeg = "AB-";
    case OPlus = "O+";
    case ONeg = "O-";

}
