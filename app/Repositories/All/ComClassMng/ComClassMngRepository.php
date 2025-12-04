<?php

namespace App\Repositories\All\ComClassMng;

use App\Models\ComClassMng;
use App\Repositories\Base\BaseRepository;

class ComClassMngRepository extends BaseRepository implements ComClassMngInterface
{
    /**
     * @var ComClassMng
     */
    protected $model;

    /**
     * ComClassMngRepository constructor.
     *
     * @param ComClassMng $model
     */
    public function __construct(ComClassMng $model)
    {
        $this->model = $model;
    }

    /**
     * Check if a className exists.
     *
     * @param string $className
     * @return bool
     */
    public function existsByClassName(string $className): bool
    {
        return $this->model->where('className', $className)->exists();
    }
}
