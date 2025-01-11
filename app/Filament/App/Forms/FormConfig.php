<?php
namespace App\Filament\App\Forms;

class FormConfig
{
    public static function columns($sm, $md, $xl, $xxl)
    {
        return [
            'sm' => $sm,
            'md' => $md,
            'xl' => $xl,
            '2xl' => $xxl,
        ];
    }
}
