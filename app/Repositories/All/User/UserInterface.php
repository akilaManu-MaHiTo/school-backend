<?php
namespace App\Repositories\All\User;

use App\Repositories\Base\EloquentRepositoryInterface;

interface UserInterface extends EloquentRepositoryInterface
{

    public function getUsersByAssigneeLevelAndSection(int $level, string $section);

    public function getByUserType(int $userType);
    public function getByIds(array $ids);
    public function search($keyword);

}
