<?php

namespace App\Http\Controllers;
use Illuminate\Database\Eloquent\Builder;

abstract class Controller
{
    public function applyFilters($query, $filter, $model) {
        if (!empty($filter->filters->global->value)) {
            $query->where(function (Builder $query) use ($filter) {
                $value = '%' . $filter->filters->global->value . '%';
                if (!class_exists($model)) {
                    throw new \Exception("Invalid model: $model");
                }
                $user = new $model();
                foreach ($user->getFillable() as $column) {
                    $query->orWhere($column, 'LIKE', $value);
                }
            });
        }
        return $query;
    }
}
