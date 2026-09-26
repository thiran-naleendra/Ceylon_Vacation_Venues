<?php

namespace App\Enums;

enum ImageProcessingStatus: string
{
    case Pending = 'pending';
    case Ready = 'ready';
    case Failed = 'failed';
}
