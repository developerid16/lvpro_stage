<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $permission_prefix, $view_file_path;
    protected $layout_data = [];

    public function get_sort_offset_limit_query($request, $query, array $search_column = [], array $searched_from_relation = [], array $relation_column = [], $relationSortQ =
    null, $default_sort = true)
    {
        /*-------- START for search Query --------*/

        //---------START for relation column search

        $request->request->add(['is_search_where_query_used' => false]); //add request
        // if (!empty($searched_from_relation) && !empty($request->search)) {
        //     foreach ($searched_from_relation as $relation => $row) {
        //         if (!empty($relation && !empty($row))) {
        //             $query->whereHas($relation, function ($q) use ($request, $row) {
        //                 $row = implode(',', $row);
        //                 $q->where(DB::raw("CONCAT($row)"), 'LIKE', "%" . $request->search . "%");
        //                 $request->is_search_where_query_used = true;
        //             });
        //         }
        //     }
        // }

        // permissions , permissions ,  permissions

        $filter = json_decode($request->filter, true);

        if (!empty($searched_from_relation) && $filter) {

            foreach ($searched_from_relation as $relation => $row) {
                foreach ($filter as $key => $value) {

                     if (in_array($key, $relation_column[$relation])) {
                        if (!empty($relation && !empty($row))) {
                            $query->whereHas($relation, function ($q) use ($value, $row, $request, $key,) {
                                $rows = implode(',', $row);
                                $q->where(DB::raw("CONCAT($rows)"), 'LIKE', "%" . $value . "%");

                                $request->is_search_where_query_used = true;
                            });
                        }
                    }
                }
            }
        }


        if (!empty($search_column) && !empty($request->search)) {

            $search_column = implode(',', $search_column);

            $query->orwhere(DB::raw("CONCAT($search_column)"), 'LIKE', "%" . $request->search . "%");
        }

        if ($request->filter && count($filter) > 0) {
            foreach ($filter as $key => $value) {
                if (in_array($key, $search_column)) {
                    $query->where($key, 'LIKE', "%" . $value . "%");
                }
            }
        }



        $request->request->remove('is_search_where_query_used');
        /*-------- END for search Query --------*/

        if (isset($request->sort) && !empty($request->sort)) {
            if (!empty($relationSortQ)) {
                $query->orderBy($relationSortQ, $request->order);
            } else {
                 $query->orderBy($request->sort, $request->order);
            }
        } else {
            if ($default_sort) {
                $query->orderBy('id', 'DESC');
            }
        }

        $count = $query->count();

        $query->when($request->offset, function ($q) use ($request) {
            $q->offset($request->offset);
        });
        $query->when($request->limit, function ($q) use ($request) {
            $q->limit($request->limit);
        });

        //return $query->get();
        return $data = [
            'data' => $query,
            'count' => $count
        ];
    }
}
