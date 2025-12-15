<?php

namespace App\Traits;

use App\Models\ComSubjects;
use Illuminate\Support\Collection;

trait HandlesBasketSubjects
{
    protected function normalizeBasketSubjectIds(mixed $rawIds): array
    {
        if (empty($rawIds)) {
            return [];
        }

        if (is_string($rawIds)) {
            $decoded = json_decode($rawIds, true);
            $rawIds = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        if (! is_array($rawIds)) {
            return [];
        }

        $ids = array_map('intval', $rawIds);

        return array_values(array_filter($ids, fn($id) => $id > 0));
    }

    protected function fetchBasketSubjects(array $subjectIds): Collection
    {
        $subjectIds = array_values(array_unique(
            array_filter(array_map('intval', $subjectIds), fn($id) => $id > 0)
        ));

        if (empty($subjectIds)) {
            return collect();
        }

        return ComSubjects::query()
            ->whereIn('id', $subjectIds)
            ->get()
            ->keyBy('id');
    }

    protected function formatBasketSubjects(array $subjectIds, Collection $subjectLookup): array
    {
        if (empty($subjectIds) || $subjectLookup->isEmpty()) {
            return [];
        }

        $grouped = [];

        foreach ($subjectIds as $subjectId) {
            $subject = $subjectLookup->get($subjectId);

            if (! $subject) {
                continue;
            }

            $groupName = $subject->basketGroup ?? 'Ungrouped';

            $grouped[$groupName] = [
                'id' => $subject->id,
                'subjectCode' => $subject->subjectCode,
                'subjectName' => $subject->subjectName,
                'subjectMedium' => $subject->subjectMedium,
                'basketGroup' => $subject->basketGroup,
                'isBasketSubject' => (bool) $subject->isBasketSubject,
            ];
        }

        return $grouped;
    }
}
