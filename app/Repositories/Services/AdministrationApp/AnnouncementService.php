<?php
namespace App\Repositories\Services\AdministrationApp;

use App\Models\AdministrationApp\Announcement;
use App\Repositories\Interfaces\AdministrationApp\AnnouncementInterface;
use Illuminate\Support\Facades\Auth;

class AnnouncementService implements AnnouncementInterface
{
    protected $model;

    public function __construct(Announcement $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $find = $this->model->find($id);
        if ($find) {
            $find->delete();
        }
        return $find;
    }

    /**
     * @inheritDoc
     */
    public function find(int $id)
    {
        return $this->model->find($id);
    }

    /**
     * @inheritDoc
     */
    public function paginate(int $page, int $limit, ?string $search = null)
    {
        $auth_user = Auth::user();
        $query = $this->model->query();
        $query->where([
            'company_id' => $auth_user->company_id,
            'status' => true,
        ]);
        if (!empty($search)) {
            $query->whereLike('title', '%'.$search.'%');
        }

        return $query->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data)
    {
        $find = $this->model->findOrFail($id);
        if ($find) {
            $find->update($data);
        }
        return $find;
    }
}
