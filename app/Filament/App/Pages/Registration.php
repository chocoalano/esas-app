<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Forms\FormUserWizard;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register;

class Registration extends Register
{
    protected ?string $maxWidth = '2xl';
    public function form(Form $form): Form
    {
        return $form
            ->schema(FormUserWizard::form());
    }

    public function register(): ?RegistrationResponse
    {
        $data = $this->form->getState();
        dd($data);
    }
}
