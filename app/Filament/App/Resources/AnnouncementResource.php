<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Forms\Attendance\FormAnnouncement;
use App\Filament\App\Resources\AnnouncementResource\Pages;
use App\Models\AdministrationApp\Announcement;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class AnnouncementResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Announcement::class;
    protected static ?string $navigationLabel = 'Announcement';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationIcon = 'heroicon-o-information-circle';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'replicate',
            'delete',
            'delete_any',
            'export',
            'import',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(FormAnnouncement::form());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->limit(20),
                TextColumn::make('title')->limit(20),
                ToggleColumn::make('status'),
                TextColumn::make('content')->html()->limit(100)
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
                Tables\Actions\EditAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
                Tables\Actions\DeleteAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
