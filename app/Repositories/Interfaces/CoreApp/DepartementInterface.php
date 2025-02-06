<?php
namespace App\Repositories\Interfaces\CoreApp;

interface DepartementInterface
{
    public function countAll(): int;
    public function existsByName(int $deptId, string $deptName);
}
