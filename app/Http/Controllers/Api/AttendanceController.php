<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdministrationApp\AttendanceRequest;
use App\Http\Resources\Attendance\AttendanceDetailResource;
use App\Http\Resources\Attendance\AttendanceLIstPaginateResource;
use App\Http\Resources\Attendance\AttendanceResource;
use App\Repositories\Interfaces\AdministrationApp\AttendanceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    protected $proses;

    public function __construct(AttendanceInterface $proses)
    {
        $this->proses = $proses;
        $this->middleware('auth');
        $this->middleware('permission:view_user::attendance|view_any_user::attendance', ['only' => ['index', 'show']]);
        $this->middleware('permission:create_user::attendance', ['only' => ['store']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function auth_all(Request $request)
    {
        try {
            $input = $request->only('search');
            $data = $this->proses->auth_all($input['search']);
            $response = AttendanceResource::collection($data);
            return $this->sendResponse($response, 'Attendance show list successfully.');
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
            $response = new AttendanceLIstPaginateResource($data);
            return $this->sendResponse($response, 'Attendance show list successfully.');
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
            $valid = Validator::make($request->all(), AttendanceRequest::presence());
            if ($valid->fails()) {
                return $this->sendError('Validation Error.', ['errors' => $valid->errors()], 422);
            }
            $input = $valid->getData();
            $created = $input['type'] === 'in' ?
                $this->proses->presence_in($input) :
                $this->proses->presence_out($input);
            return $created ?
                $this->sendResponse($created, 'Attendance created successfully.') :
                $this->sendError('Process error.', ['error' => $created], 500);
        } catch (\Throwable $e) {
            return $this->sendError('Process error.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $show = $this->proses->find($id);
            $response = new AttendanceDetailResource($show);
            return $this->sendResponse($response, 'Attendance show successfully.');
        } catch (\Throwable $e) {
            return $this->sendError('Process error.', ['error' => $e->getMessage()], 500);
        }
    }
}
