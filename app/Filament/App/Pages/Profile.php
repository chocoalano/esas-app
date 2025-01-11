<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Forms\FormUser;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Pages\Page;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.app.pages.profile';

    public ?array $data = [];

    public function mount(): void
    {
        // Mendapatkan data user dan memuat relasi-relasi terkait
        $user = User::with(
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
        )->find(auth()->id());

        // Inisialisasi array untuk data yang berhubungan dengan keluarga, pendidikan, pengalaman kerja
        $family = [];
        $formal_education = [];
        $informal_education = [];
        $work_experience = [];

        // Memasukkan data keluarga jika ada
        foreach ($user->families as $k) {
            $family[] = [
                "fullname" => $k->fullname,
                "relationship" => $k->relationship,
                "birthdate" => $k->birthdate,
                "marital_status" => $k->marital_status,
                "job" => $k->job,
            ];
        }

        // Memasukkan data pendidikan formal jika ada
        foreach ($user->formalEducations as $k) {
            $formal_education[] = [
                "institution" => $k->institution,
                "majors" => $k->majors,
                "score" => $k->score,
                "start" => $k->start,
                "finish" => $k->finish,
                "status" => $k->status,
                "certification" => $k->certification,
            ];
        }

        // Memasukkan data pendidikan informal jika ada
        foreach ($user->informalEducations as $k) {
            $informal_education[] = [
                "institution" => $k->institution,
                "start" => $k->start,
                "finish" => $k->finish,
                "type" => $k->type,
                "status" => $k->status,
                "certification" => $k->certification,
            ];
        }

        // Memasukkan data pengalaman kerja jika ada
        foreach ($user->workExperiences as $k) {
            $work_experience[] = [
                "company_name" => $k->company_name,
                "start" => $k->start,
                "finish" => $k->finish,
                "position" => $k->position,
                "status" => $k->status,
            ];
        }

        // Menyusun data untuk dikirim ke tampilan
        $this->data = [
            "company_id" => $user->company_id ?? null,
            "role_id" => $user->roles->pluck('id') ?? null,
            "nip" => $user->nip ?? null,
            "name" => $user->name ?? null,
            "email" => $user->email ?? null,
            "status" => $user->status ?? null,
            "phone" => $user->details->phone ?? null,
            "placebirth" => $user->details->placebirth ?? null,
            "datebirth" => $user->details->datebirth ?? null,
            "gender" => $user->details->gender ?? null,
            "blood" => $user->details->blood ?? null,
            "marital_status" => $user->details->marital_status ?? null,
            "religion" => $user->details->religion ?? null,
            "identity_type" => $user->address->identity_type ?? null,
            "identity_numbers" => $user->address->identity_numbers ?? null,
            "province" => $user->address->province ?? null,
            "city" => $user->address->city ?? null,
            "citizen_address" => $user->address->citizen_address ?? null,
            "residential_address" => $user->address->residential_address ?? null,
            "departement_id" => $user->employee->departement_id ?? null,
            "job_position_id" => $user->employee->job_position_id ?? null,
            "job_level_id" => $user->employee->job_level_id ?? null,
            "approval_line_id" => $user->employee->approval_line_id ?? null,
            "approval_manager_id" => $user->employee->approval_manager_id ?? null,
            "join_date" => $user->employee->join_date ?? null,
            "sign_date" => $user->employee->sign_date ?? null,
            "resign_date" => $user->employee->resign_date ?? null,
            "bank_name" => $user->employee->bank_name ?? null,
            "bank_number" => $user->employee->bank_number ?? null,
            "bank_holder" => $user->employee->bank_holder ?? null,
            "family" => $family,
            "formal_education" => $formal_education,
            "informal_education" => $informal_education,
            "work_experience" => $work_experience,
        ];
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
        dd($data);
    }
}
