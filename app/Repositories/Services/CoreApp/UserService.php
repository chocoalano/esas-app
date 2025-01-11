<?php
namespace App\Repositories\Services\CoreApp;

use App\Models\User;
use App\Repositories\Interfaces\CoreApp\UserInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService implements UserInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data)
    {
        try {
            DB::beginTransaction();
            // Hash password or fallback to a default value
            $password = $data['password'] ?? $data['password_confirmation'] ?? $data['nip'];
            $hashedPassword = bcrypt($password);
            // Create the main model
            $model = $this->model->create([
                'company_id' => $data['company_id'] ?? null,
                'nip' => $data['nip'],
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'avatar' => $data['avatar'] ?? 'default.png',
                'status' => $data['status'] ?? 'active',
            ]);
            // Assign role to the user (validate role existence)
            if (!empty($data['role'])) {
                $model->assignRole($data['role']);
            }
            // Update or create related data
            if (method_exists($this, 'updateOrCreateRelatedData')) {
                $this->updateOrCreateRelatedData($model, $data);
            }
            DB::commit();
            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creating user: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e,
            ]);
            throw new \Exception('Failed to create user. Please try again.');
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $find = $this->model->find($id);
        if ($find) {
            return $find->delete();
        }
        throw new ModelNotFoundException("User with ID {$id} not found.");
    }

    /**
     * @inheritDoc
     */
    public function find(int $id)
    {
        return $this->model
            ->with(
                'details',
                'address',
                'salaries',
                'families',
                'formalEducations',
                'informalEducations',
                'workExperiences',
                'employee'
            )->findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function paginate(int $page, int $limit, ?string $search = null)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole(['super_admin', 'Administrator']);

        $query = $this->model->newQuery();
        // Filter data berdasarkan role
        if (!$isAdmin) {
            $query->whereHas('employee', function ($q) use ($user) {
                $q->where('departement_id', $user->employee->departement_id);
            });
        }
        // Pencarian (search)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('nip', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%');
            });
        }
        // Paginate hasil query
        return $query->paginate($limit, ['*'], 'page', $page);
    }


    /**
     * @inheritDoc
     */
    public function update(int $id, array $data)
    {
        try {
            DB::beginTransaction();
            // Create main model
            $model = $this->model->with(
                'details',
                'address',
                'salaries',
                'families',
                'formalEducations',
                'informalEducations',
                'workExperiences',
                'employee'
            )->find($id);
            $model->company_id = $data['company_id'];
            $model->nip = $data['nip'];
            $model->name = $data['name'];
            $model->email = $data['email'];
            $model->status = $data['status'];
            if ($data['password']) {
                $model->password = bcrypt($data['password']);
            }
            if ($data['avatar']) {
                $model->avatar = $data['avatar'];
            }
            $model->save();
            // Assign role to the user
            $model->syncRoles($data['role']);
            $this->updateOrCreateRelatedData($model, $data);

            DB::commit();
            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creating user: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e,
            ]);
            throw new \Exception('Failed to update user. Please try again.');
        }
    }

    private function updateOrCreateRelatedData($model, $data)
    {
        // Update related details
        $model->details()->updateOrCreate([], array_filter($data, fn($key) => in_array($key, ['phone', 'placebirth', 'datebirth', 'gender', 'blood', 'marital_status', 'religion']), ARRAY_FILTER_USE_KEY));

        // Update related addresses
        $model->address()->updateOrCreate([], array_filter($data, fn($key) => in_array($key, ['identity_type', 'identity_numbers', 'province', 'city', 'citizen_address', 'residential_address']), ARRAY_FILTER_USE_KEY));

        // Update salaries
        $model->salaries()->updateOrCreate([], array_filter($data, fn($key) => in_array($key, ['basic_salary', 'payment_type']), ARRAY_FILTER_USE_KEY));

        // Update employee details
        $model->employee()->updateOrCreate([], array_filter($data, fn($key) => in_array($key, ['departement_id', 'job_position_id', 'job_level_id', 'approval_line_id', 'approval_manager_id', 'join_date', 'sign_date', 'resign_date', 'bank_name', 'bank_number', 'bank_holder']), ARRAY_FILTER_USE_KEY));

        // Bulk update family, education, and work experience
        $this->bulkUpdateOrCreate($model->families(), $data['family'] ?? []);
        $this->bulkUpdateOrCreate($model->formalEducations(), $data['formal_education'] ?? []);
        $this->bulkUpdateOrCreate($model->informalEducations(), $data['informal_education'] ?? []);
        $this->bulkUpdateOrCreate($model->workExperiences(), $data['work_experience'] ?? []);
    }

    private function bulkUpdateOrCreate($relation, $data)
    {
        try {
            $existingIds = $relation->pluck('id')->toArray();
            $newIds = array_column($data, 'id');
            $idsToDelete = array_diff($existingIds, $newIds);

            if (!empty($idsToDelete)) {
                $relation->whereIn('id', $idsToDelete)->delete();
            }

            foreach ($data as $item) {
                if (isset($item['id']) && in_array($item['id'], $existingIds)) {
                    $relation->where('id', $item['id'])->update($item);
                } else {
                    $relation->create($item);
                }
            }
            return $data;
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return $e->getMessage();
        }
    }
    /**
     * @inheritDoc
     */
    public function countAll(): int
    {
        return $this->model->count();
    }
    /**
     * @inheritDoc
     */
    public function findbyNip(string $nip)
    {
        return $this->model->with(
            'details',
            'address',
            'salaries',
            'families',
            'formalEducations',
            'informalEducations',
            'workExperiences',
            'employee'
        )->where('nip', $nip)->first();
    }
    /**
     * @inheritDoc
     */
    public function findUserHr()
    {
        return $this->model->whereHas('employee', function ($u) {
            $u->whereHas('departement', function ($d) {
                $d->where('name', 'HRGA');
            });
        })->get();
    }
    /**
     * @inheritDoc
     */
    public function login(array $data)
    {
        // Validasi input untuk memastikan 'nip' dan 'password' tersedia
        if (empty($data['indicatour']) || empty($data['password'])) {
            return [
                'success' => false,
                'message' => 'NIP dan password harus diisi.',
                'user' => null
            ];
        }

        // Cari pengguna berdasarkan NIP
        $user = $this->model
            ->where('email', $data['indicatour'])
            ->orWhere('nip', $data['indicatour'])
            ->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Pengguna dengan NIP tersebut tidak ditemukan.',
                'user' => null
            ];
        }

        // Coba autentikasi dengan email pengguna dan password yang diberikan
        $auth = Auth::attempt([
            'email' => $user->email,
            'password' => $data['password']
        ]);

        if ($auth) {
            return [
                'success' => true,
                'message' => 'Login berhasil.',
                'user' => $auth // Opsional: Tambahkan data pengguna jika diperlukan
            ];
        }

        return [
            'success' => false,
            'message' => 'Password yang dimasukkan salah.',
            'user' => null
        ];
    }
    /**
     * @inheritDoc
     */
    public function auth_update_family(array $data)
    {
        $find = $this->find(Auth::id());
        $proses = $this->bulkUpdateOrCreate($find->families(), $data ?? []);
        return $proses;
    }

    /**
     * @inheritDoc
     */
    public function auth_update_formal_education(array $data)
    {
        $find = $this->find(Auth::id());
        return $this->bulkUpdateOrCreate($find->formalEducations(), $data ?? []);
    }

    /**
     * @inheritDoc
     */
    public function auth_update_informal_education(array $data)
    {
        $find = $this->find(Auth::id());
        return $this->bulkUpdateOrCreate($find->informalEducations(), $data ?? []);
    }

    /**
     * @inheritDoc
     */
    public function auth_update_work_experience(array $data)
    {
        $find = $this->find(Auth::id());
        return $this->bulkUpdateOrCreate($find->workExperiences(), $data ?? []);
    }
    /**
     * @inheritDoc
     */
    public function update_password(int $userId, array $data)
    {
        $find = $this->find($userId);
        $validate_password = Hash::check($data['password'], $find->password);
        if ($validate_password) {
            $find->update([
                'password' => Hash::make($data['confirmation_new_password'])
            ]);
            return true;
        }
        return false;
    }
}
