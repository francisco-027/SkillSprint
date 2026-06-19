<?php

namespace App\Enums;

enum UploadStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Done       = 'done';
    case Failed     = 'failed';
}