<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Forms\FormUser;
use App\Models\User;
use App\Repositories\Interfaces\CoreApp\UserInterface;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.app.pages.profile';

    public ?array $data = [];

    public function mount(): void
    {
        $user = User::with([
            'roles',
            'company',
            'details',
            'address',
            'salaries',
            'families',
            'formalEducations',
            'informalEducations',
            'workExperiences',
            'employee'
        ])->find(auth()->id());

        if (!$user) {
            $this->data = [];
            return;
        }

        // Pastikan relasi adalah array/objek sebelum diproses dalam foreach
        $families = is_iterable($user->families) ? $user->families->toArray() : [];
        $formalEducations = is_iterable($user->formalEducations) ? $user->formalEducations->toArray() : [];
        $informalEducations = is_iterable($user->informalEducations) ? $user->informalEducations->toArray() : [];
        $workExperiences = is_iterable($user->workExperiences) ? $user->workExperiences->toArray() : [];

        $family = array_map(fn($k) => [
            "fullname" => $k['fullname'] ?? '',
            "relationship" => $k['relationship'] ?? '',
            "birthdate" => $k['birthdate'] ?? '',
            "marital_status" => $k['marital_status'] ?? '',
            "job" => $k['job'] ?? '',
        ], $families);

        $formal_education = array_map(fn($k) => [
            "institution" => $k['institution'] ?? '',
            "majors" => $k['majors'] ?? '',
            "score" => $k['score'] ?? '',
            "start" => $k['start'] ?? '',
            "finish" => $k['finish'] ?? '',
            "status" => $k['status'] ?? '',
            "certification" => $k['certification'] ?? '',
        ], $formalEducations);

        $informal_education = array_map(fn($k) => [
            "institution" => $k['institution'] ?? '',
            "start" => $k['start'] ?? '',
            "finish" => $k['finish'] ?? '',
            "type" => $k['type'] ?? '',
            "status" => $k['status'] ?? '',
            "certification" => $k['certification'] ?? '',
        ], $informalEducations);

        $work_experience = array_map(fn($k) => [
            "company_name" => $k['company_name'] ?? '',
            "start" => $k['start'] ?? '',
            "finish" => $k['finish'] ?? '',
            "position" => $k['position'] ?? '',
            "status" => $k['status'] ?? '',
        ], $workExperiences);

        $this->data = [
            "company_id" => $user->company_id ?? null,
            "role" => $user->roles?->pluck('name') ?? [],
            "nip" => $user->nip ?? '',
            "name" => $user->name ?? '',
            "email" => $user->email ?? '',
            "status" => $user->status ?? '',
            "avatar" => !empty($user->avatar) && is_array(json_decode($user->avatar, true))
                ? collect(json_decode($user->avatar, true))
                    ->map(fn($path) => [
                        'name' => pathinfo($path, PATHINFO_BASENAME),
                        'url' => Storage::url($path),
                    ])->toArray()
                : [],
            "phone" => $user->details->phone ?? '',
            "placebirth" => $user->details->placebirth ?? '',
            "datebirth" => $user->details->datebirth ?? '',
            "gender" => $user->details->gender ?? '',
            "blood" => $user->details->blood ?? '',
            "marital_status" => $user->details->marital_status ?? '',
            "religion" => $user->details->religion ?? '',
            "identity_type" => $user->address->identity_type ?? '',
            "identity_numbers" => $user->address->identity_numbers ?? '',
            "province" => $user->address->province ?? '',
            "city" => $user->address->city ?? '',
            "citizen_address" => $user->address->citizen_address ?? '',
            "residential_address" => $user->address->residential_address ?? '',
            "departement_id" => $user->employee->departement_id ?? null,
            "job_position_id" => $user->employee->job_position_id ?? null,
            "job_level_id" => $user->employee->job_level_id ?? null,
            "approval_line_id" => $user->employee->approval_line_id ?? null,
            "approval_manager_id" => $user->employee->approval_manager_id ?? null,
            "join_date" => $user->employee->join_date ?? '',
            "sign_date" => $user->employee->sign_date ?? '',
            "resign_date" => $user->employee->resign_date ?? '',
            "bank_name" => $user->employee->bank_name ?? '',
            "bank_number" => $user->employee->bank_number ?? '',
            "bank_holder" => $user->employee->bank_holder ?? '',
            "family" => $family,
            "formal_education" => $formal_education,
            "informal_education" => $informal_education,
            "work_experience" => $work_experience,
        ];
        // dd($this->data);
    }

    public function form(Form $form): Form
    {
        return $form->schema(FormUser::form())
            ->statePath('data')
            ->model(auth()->user());
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('Update Profile')
                ->color('primary')
                ->submit('Update'),
        ];
    }

    public function update()
    {
        $data = $this->form->getState();
        $data['role'] = Auth::user()->getRoleNames();
        $data['basic_salary'] = $data['basic_salary'] ?? 0;
        $data['payment_type'] = $data['payment_type'] ?? "Monthly";

        $exec = app(UserInterface::class)->update(auth()->id(), $data);

        return $exec ? Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send()
            : Notification::make()
                ->title('Saved unsuccessfully')
                ->body($exec)
                ->danger()
                ->send();
    }
}
