<?php

namespace App\Enums;

enum FlashcardStatus: string
{
    case Unseen   = 'unseen';
    case Current  = 'current';
    case Saved    = 'saved';
    case Mastered = 'mastered';
}