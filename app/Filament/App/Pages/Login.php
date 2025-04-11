<?php
namespace App\Filament\App\Pages;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\View;
use Filament\Pages\Auth\Login as BaseAuth;

class Login extends BaseAuth
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNipFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
                View::make('filament.components.download-android-link'),
            ])
            ->statePath('data');
    }

    protected function getNipFormComponent(): Component
    {
        return TextInput::make('nip')
            ->label('NIP')
            ->numeric()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $cek = User::where([
            'nip' => $data['nip'],
            'status' => 'active'
        ])->first();
        if ($cek) {
            return [
                'nip' => $cek->nip,
                'password' => $data['password'],
            ];
        }
    }
}
