<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Permit\PermitTypeJsonResource;
use App\Repositories\Interfaces\AdministrationApp\PermitInterface;

class PermitTypeController extends Controller
{
    protected $proses;

    public function __construct(PermitInterface $proses)
    {
        $this->proses = $proses;
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = $this->proses->type();
            $response = PermitTypeJsonResource::collection($data);
            return $this->sendResponse($response, 'Permit type show list successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Process errors.', ['error' => $e->getMessage()]);
        }
    }
}
