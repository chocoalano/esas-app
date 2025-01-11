<?php
namespace App\Filament\App\Forms\Attendance;

use App\Filament\App\Forms\FormConfig;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class FormAnnouncement
{
    public static function form()
    {
        return [
            Section::make([
                Select::make('company_id')
                ->relationship('company', 'name')
                ->searchable()
                ->preload()
                ->required(),
                TextInput::make('title')
                    ->required(),
                Toggle::make('status')
                    ->inline(false)
                    ->required(),
                RichEditor::make('content')
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])
                    ->extraInputAttributes(['style' => 'min-height: 20rem; max-height: 100vh; overflow-y: auto;'])
                    ->columnSpanFull()
                    ->required()
            ])
                ->columns(FormConfig::columns(1, 3, 3, 3))
        ];
    }
}
