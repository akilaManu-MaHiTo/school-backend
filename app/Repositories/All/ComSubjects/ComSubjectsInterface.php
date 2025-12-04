<?php

namespace App\Repositories\All\ComSubjects;

use App\Repositories\Base\EloquentRepositoryInterface;

interface ComSubjectsInterface extends EloquentRepositoryInterface
{
    /**
     * Check if a subject value already exists.
     *
     * @param  int|string  $subject
     * @return bool
     */
    public function existsBySubject($subject): bool;
    public function existByCode($subject): bool;
}
