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

    public function searchTeachers($keyword)
    {
        return User::where('employeeType', 'Teacher')
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%")
                    ->orWhere('userName', 'LIKE', "%$keyword%")
                    ->orWhere('email', 'LIKE', "%$keyword%");
            })
            ->get();
    }

    public function searchParents($keyword)
    {
        return User::where('employeeType', 'Parent')
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%")
                    ->orWhere('userName', 'LIKE', "%$keyword%")
                    ->orWhere('email', 'LIKE', "%$keyword%");
            })
            ->get();
    }

    public function searchStudents($keyword)
    {
        return User::where('employeeType', 'Student')
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%")
                    ->orWhere('userName', 'LIKE', "%$keyword%")
                    ->orWhere('email', 'LIKE', "%$keyword%");
            })
            ->get();
    }

    public function searchStaffs($keyword)
    {
        return User::where('employeeType', 'Staff')
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%")
                    ->orWhere('userName', 'LIKE', "%$keyword%")
                    ->orWhere('email', 'LIKE', "%$keyword%");
            })
            ->get();
    }
}
