<?php

function executeQuery($query) {
    $data = [];

    if (request()->has('search') && request('search') != '') {
        $columns = explode(',', request('search_columns'));

        $query->where(function($q) use ($columns) {
            foreach($columns as $column) {
                if (count(explode('.', $column)) === 3) {
                    $columnItems = explode('.', $column);

                    $q->orWhereHas($columnItems[0], function($s) use($columnItems) {
                        $s->whereHas($columnItems[1], function($s) use($columnItems) {
                            $s->where($columnItems[2], 'LIKE', '%' . request('search') . '%');
                        });
                    });
                } elseif (count(explode('.', $column)) === 2) {
                    $columnItems = explode('.', $column);

                    $q->orWhereHas($columnItems[0], function($s) use($columnItems) {
                        $s->where($columnItems[1], 'LIKE', '%' . request('search') . '%');
                    });
                } else {
                    $q->orWhere($column, 'LIKE', '%' . request('search') . '%');
                }
            }
        });
    }

    if (request()->has('sort') && request('sort') != '') {
        if (count(explode('.', request('sort'))) > 1) {
            $query->orderByPowerJoins(request('sort'), request('sort_order'), null, 'leftJoin');
        } else {
            $query->orderBy(request('sort'), request('sort_order'));
        }
    }

    if (request()->has('per_page')) {
        $data = $query->paginate(request('per_page'));
    } else {
        $data = $query->get();
    }

    return $data;
}
