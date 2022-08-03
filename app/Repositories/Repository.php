<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

abstract class Repository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function query(array $argument = [], bool $execute = false): Collection|Builder|array
    {
        $query = $this->model::query();
        $order = explode('|', Arr::get($argument, 'orderBy', 'id|asc'));
        $param = [
            'with'   => Arr::get($argument, 'with', []),
            'select' => Arr::get($argument, 'select', ['*']),
            'where'  => Arr::get($argument, 'where', []),
            'skip'   => Arr::get($argument, 'skip', 0),
            'take'   => Arr::get($argument, 'take', 999999),
        ];

        foreach ($param as $chain => $value) {
            $query->$chain($value);
        }

        $query->orderBy($order[0], $order[1]);
        return $execute ? $query->get() : $query;
    }

    public function create(array $argument): Model
    {
        $query = $this->model::query();
        return $query->create($argument);
    }

    public function insert(array $argument): void
    {
        $query = $this->model::query();
        $query->insert($argument);
    }

    public function update(Model $model, array $argument): void
    {
        $model->update($argument);
    }
}