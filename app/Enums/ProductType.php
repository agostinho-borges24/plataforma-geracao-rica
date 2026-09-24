<?php

namespace App\Enums;

enum ProductType: string
{
    case Course = 'course';
    case Ebook = 'ebook';

    public function label(): string
    {
        return match ($this) {
            self::Course => 'Curso',
            self::Ebook => 'E-book',
        };
    }
}