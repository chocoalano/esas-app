<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ProfilePartialUpdateRequest;
use App\Http\Requests\User\ProfileUpdateRequest;
use App\Http\Requests\User\UserCreateRequest;
use App\Http\Resources\User\UserFamilyResource;
use App\Http\Resources\User\UserFormalEducationResource;
use App\Http\Resources\User\UserInformalEducationResource;
use App\Http\Resources\User\UserListPaginationResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserWorkExperienceResource;
use App\Models\User;
use App\Repositories\Interfaces\CoreApp\UserInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $proses;

    public function __construct(UserInterface $proses)
    {
        $this->proses = $proses;
        // $this->middleware('auth');
        $this->middleware('permission:view_user|view_any_user', ['only' => ['index', 'show']]);
        $this->middleware('permission:create_user', ['only' => ['store']]);
        $this->middleware('permission:update_user', ['only' => ['update']]);
        $this->middleware('permission:delete_user|delete_any_user', ['only' => ['destroy']]);
    }
    /**
     * Store a register users sign up.
     */
    public function register(Request $request)
    {
        try {
            $valid = Validator::make($request->all(), UserCreateRequest::rules());
            if ($valid->fails()) {
                return $this->sendError('Validation Error.', ['error' => $valid->errors()], 422);
            }
            $register = $this->proses->create($valid->getData());
            $login = [
                'email' => $register->email,
                'password' => $valid->getData()['password'],
            ];
            $proses = $this->proses->login($login);
            if ($proses) {
                $user = Auth::user();
                $success['token'] = $user->createToken('esas-app')->plainTextToken;
                $success['name'] = $user->name;

                return $this->sendResponse($success, 'User registration successfully.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Registration failed.', ['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Store a users sign in.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'indicatour' => 'required|exists:users,nip',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }
        $proses = $this->proses->login($validator->getData());
        if ($proses['success']) {
            $user = Auth::user();
            $success['token'] = $user->createToken('esas-app')->plainTextToken;
            $success['token_type'] = 'Bearer';
            $success['name'] = $user->name;
            return $this->sendResponse($success, 'User login successfully.');
        }
        return $this->sendError('Unauthorised.', ['error' => $proses['message']]);
    }
    /**
     * Store a users logout.
     */
    public function logout(Request $request)
    {
        try {
            $logout = $request->user()->tokens()->delete();
            return $this->sendResponse($logout, 'User logout successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()]);
        }
    }
    /**
     * Store a users profile detail.
     */
    public function profile()
    {
        try {
            $detail = $this->proses->find(Auth::user()->id);
            return $this->sendResponse(new UserResource($detail), 'User detail access successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()]);
        }
    }
    /**
     * Store a users profile update.
     */
    public function profile_update(Request $request)
    {
        try {
            $valid = Validator::make($request->all(), ProfileUpdateRequest::rules());

            if ($valid->fails()) {
                return $this->sendError('Validation Error.', $valid->errors(), 422);
            }

            $detail = $this->proses->update(Auth::user()->id, $valid->getData());
            return $this->sendResponse(new UserResource($detail), 'User detail update successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()]);
        }
    }
    /**
     * Store a users profile update family.
     */
    public function profile_update_family(Request $request)
    {
        try {
            $valid = Validator::make($request->all(), ProfilePartialUpdateRequest::family());

            if ($valid->fails()) {
                return $this->sendError('Validation Error.', $valid->errors(), 422);
            }
            $this->proses->auth_update_family($valid->getData());
            $new_family = $this->proses->find(Auth::user()->id);
            $response = UserFamilyResource::collection($new_family->families);
            return $this->sendResponse($response, 'User detail family successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()]);
        }
    }
    /**
     * Store a users profile update formal information.
     */
    public function profile_update_formal_education(Request $request)
    {
        try {
            $valid = Validator::make($request->all(), ProfilePartialUpdateRequest::formal_education());

            if ($valid->fails()) {
                return $this->sendError('Validation Error.', $valid->errors(), 422);
            }
            $this->proses->auth_update_formal_education($valid->getData());
            $new_formal = $this->proses->find(Auth::user()->id);
            $response = UserFormalEducationResource::collection($new_formal->formalEducations);
            return $this->sendResponse($response, 'User detail formal education successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()]);
        }
    }
    /**
     * Store a users profile update informal information.
     */
    public function profile_update_informal_education(Request $request)
    {
        try {
            $valid = Validator::make($request->all(), ProfilePartialUpdateRequest::informal_education());

            if ($valid->fails()) {
                return $this->sendError('Validation Error.', $valid->errors(), 422);
            }
            $this->proses->auth_update_informal_education($valid->getData());
            $new_informal = $this->proses->find(Auth::user()->id);
            $response = UserInformalEducationResource::collection($new_informal->informalEducations);
            return $this->sendResponse($response, 'User detail informal education successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()]);
        }
    }
    /**
     * Store a users profile update work experience.
     */
    public function profile_update_work_experience(Request $request)
    {
        try {
            $valid = Validator::make($request->all(), ProfilePartialUpdateRequest::work_experience());

            if ($valid->fails()) {
                return $this->sendError('Validation Error.', $valid->errors(), 422);
            }
            $this->proses->auth_update_work_experience($valid->getData());
            $new_work_experience = $this->proses->find(Auth::user()->id);
            $response = UserWorkExperienceResource::collection($new_work_experience->workExperiences);
            return $this->sendResponse($response, 'User detail work experience successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()]);
        }
    }
    /**
     * Store a users profile update password.
     */
    public function profile_update_password(Request $request)
    {
        try {
            $valid = Validator::make($request->all(), ProfilePartialUpdateRequest::update_password());

            if ($valid->fails()) {
                return $this->sendError('Validation Error.', $valid->errors(), 422);
            }
            $update = $this->proses->update_password(Auth::user()->id, $valid->getData());
            if ($update) {
                return $this->sendResponse($valid->getData(), 'User detail update password successfully.');
            }
            return $this->sendError('Process errors.', ['error' => 'old password not match'], 500);
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()]);
        }
    }
    /**
     * Store a users profile permission.
     */
    public function profile_permission()
    {
        try {
            $user = Auth::user();
            $permission = $user->getAllPermissions();
            return $this->sendResponse($permission->pluck('name'), 'User detail update password successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()]);
        }
    }
    /**
     * Store a users profile avatar.
     */
    public function profile_avatar()
    {
        try {
            $user = Auth::user();
            if (
                !Storage::disk(env('FILESYSTEM_DISK'))
                    ->exists("avatar-users/{$user->avatar}")
            ) {
                return response()->json([
                    'message' => 'Image not found',
                ], 404);
            }
            $path = Storage::disk(env('FILESYSTEM_DISK'))
                ->path("avatar-users/{$user->avatar}");
            return response()->file($path);
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()]);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $input = $request->only('page', 'limit', 'search');
            $data = $this->proses->paginate($input['page'], $input['limit'], $input['search']);
            $response = new UserListPaginationResource($data);
            return $this->sendResponse($response, 'User show list successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $valid = Validator::make($request->all(), UserCreateRequest::rules());
            if ($valid->fails()) {
                return $this->sendError('Validation Error.', ['error' => $valid->errors()], 422);
            }
            $register = $this->proses->create($valid->getData());
            return $this->sendResponse(new UserResource($register), 'User created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $data = $this->proses->find($id);
            return $this->sendResponse(new UserResource($data), 'User show detail successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id, Request $request)
    {
        try {
            $valid = Validator::make($request->all(), UserCreateRequest::rules());
            if ($valid->fails()) {
                return $this->sendError('Validation Error.', ['error' => $valid->errors()], 422);
            }
            $data = $this->proses->update($id, $valid->getData());
            return $this->sendResponse(new UserResource($data), 'User updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $data = $this->proses->delete($id);
            return $this->sendResponse(new UserResource($data), 'User delete successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()], 500);
        }
    }
}
