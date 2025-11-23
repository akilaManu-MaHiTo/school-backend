<?php
namespace App\Repositories\All\User;

use App\Models\User;
use App\Repositories\Base\BaseRepository;

// repository Class
class UserRepository extends BaseRepository implements UserInterface
{
    /**
     * @var User
     */
    protected $model;

    /**
     * BaseRepository constructor.
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }
    public function getUsersByAssigneeLevelAndSection(int $level, string $section)
    {
        return User::where('assigneeLevel', $level)
            ->whereJsonContains('responsibleSection', $section)
            ->get();
    }

    public function getByUserType($userType)
    {
        return User::where('userType', $userType)->get();
    }
    public function getByIds(array $ids)
    {
        return User::whereIn('id', $ids)->get()->keyBy('id');
    }

    public function search($keyword)
    {
        return User::where('name', 'like', '%' . $keyword . '%')
            ->orWhere('email', 'like', '%' . $keyword . '%')
            ->get();
    }

}
