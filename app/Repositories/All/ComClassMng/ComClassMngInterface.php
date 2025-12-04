<?php

namespace App\Repositories\All\ComClassMng;

use App\Repositories\Base\EloquentRepositoryInterface;

interface ComClassMngInterface extends EloquentRepositoryInterface
{
    /**
     * Check if a className value already exists.
     *
     * @param string $className
     * @return bool
     */
    public function existsByClassName(string $className): bool;
}
