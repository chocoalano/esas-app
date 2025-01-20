<?php

namespace App\Filament\App\Resources\UserResource\Pages;

use App\Filament\App\Resources\UserResource;
use App\Models\User;
use App\Repositories\Interfaces\CoreApp\UserInterface;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = User::with([
            'roles',
            'company',
            'details',
            'salaries',
            'families',
            'formalEducations',
            'informalEducations',
            'workExperiences',
            'employee',
            'address',
        ])->find($data['id']);

        if (!$user) {
            return $data;
        }
        $role = [];
        foreach ($user->roles->toArray() as $k) {
            array_push($role, $k['name']);
        }
        $data = array_merge($data, ['role' => $role]);

        // Mengisi data dari `details`
        $data = array_merge($data, [
            'phone' => $user->details?->phone ?? '',
            'placebirth' => $user->details?->placebirth ?? '',
            'datebirth' => $user->details?->datebirth ?? '',
            'gender' => $user->details?->gender ?? '',
            'blood' => $user->details?->blood ?? '',
            'marital_status' => $user->details?->marital_status ?? '',
            'religion' => $user->details?->religion ?? '',
        ]);

        // Mengisi data dari `address`
        $data = array_merge($data, [
            'identity_type' => $user->address?->identity_type ?? '',
            'identity_numbers' => $user->address?->identity_numbers ?? '',
            'province' => $user->address?->province ?? '',
            'city' => $user->address?->city ?? '',
            'citizen_address' => $user->address?->citizen_address ?? '',
            'residential_address' => $user->address?->residential_address ?? '',
        ]);

        // Mengisi data dari `employee`
        $data = array_merge($data, [
            'departement_id' => $user->employee?->departement_id ?? null,
            'job_position_id' => $user->employee?->job_position_id ?? null,
            'job_level_id' => $user->employee?->job_level_id ?? null,
            'approval_line_id' => $user->employee?->approval_line_id ?? null,
            'approval_manager_id' => $user->employee?->approval_manager_id ?? null,
            'join_date' => $user->employee?->join_date ?? null,
            'sign_date' => $user->employee?->sign_date ?? null,
            'resign_date' => $user->employee?->resign_date ?? null,
            'bank_name' => $user->employee?->bank_name ?? '',
            'bank_number' => $user->employee?->bank_number ?? '',
            'bank_holder' => $user->employee?->bank_holder ?? '',
            'basic_salary' => $user->salaries?->basic_salary ?? 0,
            'payment_type' => $user->salaries?->payment_type ?? 'Monthly',
        ]);

        // Menyiapkan array keluarga
        $data['family'] = $user->families?->map(fn($family) => [
            'fullname' => $family->fullname,
            'relationship' => $family->relationship,
            'birthdate' => $family->birthdate,
            'marital_status' => $family->marital_status,
            'job' => $family->job,
        ])->toArray() ?? [];

        // Menyiapkan array pendidikan formal
        $data['formal_education'] = $user->formalEducations?->map(fn($education) => [
            'institution' => $education->institution,
            'majors' => $education->majors,
            'score' => $education->score,
            'start' => $education->start,
            'finish' => $education->finish,
            'status' => $education->status,
            'certification' => $education->certification,
        ])->toArray() ?? [];

        // Menyiapkan array pendidikan nonformal
        $data['informal_education'] = $user->informalEducations?->map(fn($education) => [
            'institution' => $education->institution,
            'start' => $education->start,
            'finish' => $education->finish,
            'type' => $education->type,
            'status' => $education->status,
            'certification' => $education->certification,
        ])->toArray() ?? [];

        // Menyiapkan array pengalaman kerja
        $data['work_experience'] = $user->workExperiences?->map(fn($experience) => [
            'company_name' => $experience->company_name,
            'start' => $experience->start,
            'finish' => $experience->finish,
            'position' => $experience->position,
            'status' => $experience->status,
            'certification' => $experience->certification,
        ])->toArray() ?? [];
        // dd($data);
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UserInterface::class)->update($record->id, $data);
    }
}
