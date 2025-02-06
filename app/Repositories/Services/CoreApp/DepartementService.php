<?php
namespace App\Repositories\Services\CoreApp;

use App\Models\CoreApp\Departement;
use App\Repositories\Interfaces\CoreApp\DepartementInterface;

class DepartementService implements DepartementInterface
{
    protected $model;

    public function __construct(Departement $model)
    {
        $this->model = $model;
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
    public function existsByName(int $deptId, string $deptName)
    {
        return $this->model->where(function ($d) use ($deptId, $deptName) {
            $d->where('departement_id', $deptId)->where('name', $deptName);
        })->first();
    }
}
