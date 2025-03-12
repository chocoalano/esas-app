<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Forms\Attendance\FormPermit;
use App\Filament\App\Resources\PermitResource\Pages;
use App\Filament\App\Tables\Administration\TablePermit;
use App\Models\AdministrationApp\Permit;
use App\Models\User;
use App\Repositories\Interfaces\AdministrationApp\PermitInterface;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class PermitResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Permit::class;
    protected static ?string $navigationLabel = 'Permit';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationIcon = 'gmdi-request-page-o';
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
        return $form->schema(FormPermit::form());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(TablePermit::table())
            ->filters(TablePermit::filter(), layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ReplicateAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
                    Tables\Actions\Action::make('approval')
                        ->icon('fluentui-signature-24')
                        ->visible(fn(Permit $record): bool => self::isApproveAuthorized($record))
                        ->modalHidden(fn(Permit $record): bool => !self::isApproveAuthorized($record))
                        ->modalHeading('Approval Confirmation')
                        ->modalDescription("Are you sure you want to approve this permit?")
                        ->modalIcon('heroicon-o-check-circle')
                        ->modalWidth(MaxWidth::Small)
                        ->modalAlignment(Alignment::Center)
                        ->form([
                            ToggleButtons::make('status')
                                ->options([
                                    'w' => 'Waiting',
                                    'n' => 'Rejected',
                                    'y' => 'Approved',
                                ])
                                ->grouped()
                                ->required(),
                            Textarea::make('notes')
                                ->columnSpanFull()
                                ->required(),
                        ])
                        ->action(function (array $data, Permit $record): void {
                            $permitService = app(PermitInterface::class);
                            $action = $permitService->approved(
                                $record->id,
                                auth()->id(),
                                $data['status'],
                                $data['notes']
                            );

                            $recipient = User::find($record->user_id);

                            if ($action) {
                                Notification::make()
                                    ->title('Approval Successful')
                                    ->success()
                                    ->sendToDatabase($recipient)
                                    ->broadcast($recipient)
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Approval Failed')
                                    ->danger()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(auth()->user()->hasAnyRole(['super_admin', 'Administrator']) ? true : false),
                ]),
            ])->paginated([5, 10, 15, 20]);
    }

    public static function isApproveAuthorized(Permit $record): bool
    {
        $cek = $record
            ->approvals()
            ->where('user_id', auth()->id())
            ->where('user_approve', 'w')
            ->exists();
        return $cek;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePermits::route('/'),
        ];
    }
}
