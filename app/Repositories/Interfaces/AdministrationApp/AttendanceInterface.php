<?php
namespace App\Repositories\Interfaces\AdministrationApp;

interface AttendanceInterface
{
    public function paginate(int $page, int $limit, string $search);
    public function countAll();
    public function chart(string $filter);
    public function auth_all(int $month);
    public function find(int $id);
    public function findbySchedule(int $id);
    public function create(array $data);
    public function presence_in(array $data);
    public function presence_out(array $data);
    public function update(int $id, array $data);
    public function delete(int $id): bool;
}
