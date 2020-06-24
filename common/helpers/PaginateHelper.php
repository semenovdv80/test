<?php
namespace common\helpers;

use yii\data\Pagination;

class PaginateHelper
{
    /**
     * @param $query
     * @param $request
     * @return mixed
     */
    public static function paginate($query, $request)
    {
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $pagination->pageSizeParam = 'per_page';
        $pagination->pageSize = $request['per_page'] ?? 25;

        $records = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $records['pagination'] = $pagination;

        $records['from'] = $pagination->offset +1;
        $records['to'] = $pagination->page +1 < $pagination->pageCount ?
            $pagination->offset +$pagination->pageSize :
            $pagination->totalCount;

        return $records;
    }
}