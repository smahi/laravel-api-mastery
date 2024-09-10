<?php

namespace App\Filters\V1;

class TicketFilter extends QueryFilter
{
    protected $sortable = [
        'title',
        'status',
        'createdAt' => 'created_at'
    ];

    public function status($value)
    {
        $arr = explode(",", $value);
        $this->builder->whereIn("status", $arr);
    }

    public function title($value)
    {
        $like = str_replace("*", "%", $value);
        $this->builder->whereLike('title', $like);
    }

    public function sort($value)
    {
        $direction = 'asc';
        $columns = explode(',', $value);

        foreach ($columns as $column) {
            if (strpos($column, '-') === 0) {
                $direction = 'desc';
                $column = substr($column, 1);
            } else {
                $direction = 'asc';
            }

            if (array_key_exists($column, $this->sortable)) {
                $column = $this->sortable[$column];
            }

            $this->builder->orderBy($column, $direction);
        }
    }

    public function createdAt($value)
    {
        $range = explode(',', $value);

        if (count($range) == 2) {
            return $this->builder->whereBetween('created_at', $range);
        }

        $this->builder->whereDate('created_at', '=', $range[0]);
    }

    public function filter($value)
    {
        foreach ($value as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }
    }
}
