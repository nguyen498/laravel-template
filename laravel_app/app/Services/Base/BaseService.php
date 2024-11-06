<?php

/**
 * Created by PhpStorm.
 * User: Tuan
 * Date: 12/21/2018
 * Time: 2:24 PM
 */

namespace App\Services\Base;

use App\Lib\Models\GeoDistance;
use App\Lib\Models\MustNot;
use App\Lib\Models\Prefix;
use App\Lib\Models\QuerySort;
use App\Utils\SqlUtil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use JeroenG\Explorer\Domain\Syntax\MatchAll;
use JeroenG\Explorer\Domain\Syntax\Matching;
use JeroenG\Explorer\Domain\Syntax\MatchPhrase;
use JeroenG\Explorer\Domain\Syntax\MultiMatch;
use JeroenG\Explorer\Domain\Syntax\QueryString;
use JeroenG\Explorer\Domain\Syntax\Range;
use JeroenG\Explorer\Domain\Syntax\Term;
use JeroenG\Explorer\Domain\Syntax\Terms;
use JeroenG\Explorer\Infrastructure\Scout\ElasticEngine;

abstract class BaseService
{
    protected $repo_base;
    protected $with;
    protected $is_app;

    abstract public function getModelName();
    abstract public function getTableName();

    public function getAll()
    {
        $this->is_app = false;

        $datas = $this->repo_base->findAll();
        $count = $this->repo_base->countAll();
        return [
            'code' => '200',
            'data' => [
                'data' => $this->formatSelectData($datas),
                'total' => $count
            ]
        ];
    }

    public function store($inputs)
    {
        $this->is_app = isset($inputs['is_app']) ? $inputs['is_app'] : false;

        $validate = $this->checkInputs($inputs, null);
        if ($validate['is_failed']) {
            return $validate;
        }
        $input_data = $validate['inputs'];
        $data = $this->repo_base->create($input_data);
        $data = $this->repo_base->findById($data->id, $this->with);
        return [
            'code' => '200',
            'data' => $this->formatData($data)
        ];
    }

    public function update($id, $inputs)
    {
        $this->is_app = isset($inputs['is_app']) ? $inputs['is_app'] : false;

        /** @var Model $model */
        $data = $this->repo_base->findById($id);
        if (!isset($data)) {
            return ['code' => '004', 'message' => $this->getModelName()];
        }
        $validate = $this->checkInputs($inputs, $id);
        if ($validate['is_failed']) {
            return $validate;
        }
        $input_dat = $validate['inputs'];

        $this->repo_base->update($id, $input_dat);
        $data = $this->repo_base->findById($data->id, $this->with);
        return [
            'code' => '200',
            'data' => $this->formatData($data)
        ];
    }

    public function findById($id)
    {
        $this->is_app = false;
        /** @var Model $model */
        $data = $this->repo_base->findById($id, $this->with);
        if (!isset($data)) {
            return ['success' => false, 'code' => '004', 'message' => $this->getModelName()];
        }
        return [
            'success' => true,
            'code' => '200',
            'data' => $this->formatData($data)
        ];
    }

    public function destroy($id)
    {
        $this->is_app = false;
        $data = $this->repo_base->findById($id);

        if (!isset($data)) {
            return ['code' => '004', 'message' => $this->getModelName()];
        }
        $data->delete();

        return [
            'code' => '200',
            'message' => 'Deleted successfully'
        ];
    }

    public function search($inputs)
    {
        $this->is_app = isset($inputs['is_app']) ? $inputs['is_app'] : false;
        $text = null;
        $columns = [];
        $columnsHas = [];
        $term = isset($inputs['term']) ? $inputs['term'] : [];
        $with = isset($inputs['with']) ? $inputs['with'] : $this->with;
        $page = isset($inputs['page']) ? $inputs['page'] : 1;
        $limit = isset($inputs['limit']) ? $inputs['limit'] : 30;
        $orderBy = isset($inputs['order_by']) ? $inputs['order_by'] : 'created_at';
        $sort = isset($inputs['sort']) ? $inputs['sort'] : 'desc';
        $filter = isset($inputs['filter']) ? $inputs['filter'] : [];
        $joins = $this->getJoinTable();

        $orderBy = $this->generateOrder($orderBy);
        $select = $this->generateSelect($inputs, $this->getTableName());
        // status
        $columns = $this->generateColumn($filter, $columns);
        // generate conditions from term
        $query = $this->generateQuery($term, $columns);
        $columns = $query['columns'];
        $text = $query['search'];

        $datas = $this->repo_base->searchText($text, $columns, $columnsHas, $joins, $page, $limit, $orderBy, $sort, $with, $select);
        $count = $this->repo_base->searchTextCount($text, $columns, $columnsHas, $joins);
        return [
            'code' => '200',
            'data' => [
                'data' => $this->formatSelectData($datas),
                'total' => $count
            ]
        ];
    }

    public function getDictByIds($inputs)
    {
        $this->is_app = isset($inputs['is_app']) ? $inputs['is_app'] : false;

        if (!isset($inputs['ids'])) {
            return ['code' => '003', 'message' => 'ids '];
        }
        $orderBy = isset($inputs['order_by']) ? $inputs['order_by'] : 'id';
        $sort = isset($inputs['sort']) ? $inputs['sort'] : 'asc';
        $withTrashed = isset($inputs['with_trashed']) ? $inputs['with_trashed'] : false;
        $select = $this->generateSelect($inputs, $this->getTableName());
        $with = isset($inputs['with']) ? $inputs['with'] : [];

        $datas = $this->repo_base->dictByIds($inputs['ids'], $orderBy, $sort, $withTrashed, $select, $with);

        $res = [];
        foreach ($datas as $key => $data) {
            $res[$key] = $this->formatData($data);
        }

        return [
            'code' => '200',
            'data' => $res
        ];
    }

    public function getDictByIdAndConds($inputs)
    {
        $this->is_app = isset($inputs['is_app']) ? $inputs['is_app'] : false;
        if (!isset($inputs['ids'])) {
            return ['code' => '003', 'message' => 'ids '];
        }
        $orderBy = isset($inputs['order_by']) ? $inputs['order_by'] : 'id';
        $sort = isset($inputs['sort']) ? $inputs['sort'] : 'asc';
        $withTrashed = isset($inputs['with_trashed']) ? $inputs['with_trashed'] : false;
        $select = $this->generateSelect($inputs, $this->getTableName());
        $with = isset($inputs['with']) ? $inputs['with'] : [];
        $conds = isset($inputs['conds']) ? $inputs['conds'] : [];

        $datas = $this->repo_base->dictByIdAndConds($inputs['ids'], $conds, $orderBy, $sort, $withTrashed, $select, $with);

        $res = [];
        foreach ($datas as $key => $data) {
            $res[$key] = $this->formatData($data);
        }

        return [
            'code' => '200',
            'data' => $res
        ];
    }

    public function getDictByObjectIdAndConds($inputs)
    {
        $this->is_app = isset($inputs['is_app']) ? $inputs['is_app'] : false;
        if (!isset($inputs['ids'])) {
            return ['code' => '003', 'message' => 'ids '];
        }
        $objectKey = isset($inputs['object_key']) ? $inputs['object_key'] : 'id';
        $orderBy = isset($inputs['order_by']) ? $inputs['order_by'] : 'id';
        $sort = isset($inputs['sort']) ? $inputs['sort'] : 'asc';
        $withTrashed = isset($inputs['with_trashed']) ? $inputs['with_trashed'] : false;
        $select = $this->generateSelect($inputs, $this->getTableName());
        $with = isset($inputs['with']) ? $inputs['with'] : [];
        $conds = isset($inputs['conds']) ? $inputs['conds'] : [];

        $datas = $this->repo_base->dictByObjectIdAndConds($objectKey, $inputs['ids'], $conds, $orderBy, $sort, $withTrashed, $select, $with);

        $res = [];
        foreach ($datas as $key => $data) {
            $res[$key] = $this->formatData($data);
        }

        return [
            'code' => '200',
            'data' => $res
        ];
    }

    public function getDictByColumns($inputs)
    {
        $this->is_app = isset($inputs['is_app']) ? $inputs['is_app'] : false;

        $columns = isset($inputs['conds']) ? $inputs['conds'] : [];
        $columnKey = isset($inputs['column_key']) ? $inputs['column_key'] : 'id';
        $orderBy = isset($inputs['order_by']) ? $inputs['order_by'] : 'id';
        $sort = isset($inputs['sort']) ? $inputs['sort'] : 'asc';
        $withTrashed = isset($inputs['with_trashed']) ? $inputs['with_trashed'] : false;
        $select = $this->generateSelect($inputs, $this->getTableName());
        $with = isset($inputs['with']) ? $inputs['with'] : [];

        $datas = $this->repo_base->dictByWhere($columns, $columnKey, $orderBy, $sort, $withTrashed, $select, $with);

        $res = [];
        foreach ($datas as $key => $data) {
            $res[$key] = $this->formatData($data);
        }
        return [
            'code' => '200',
            'data' => $res
        ];
    }

    public function generateOrder($orderBy)
    {
        return $orderBy;
    }

    public function generateColumn($inputs, $columns)
    {
        return $columns;
    }

    public function generateSelect($inputs, $table)
    {
        $select = [$table . '.*'];
        if (isset($inputs['select']) && $inputs['select'] != '*') {
            $select = [];
            $split = explode(',', $inputs['select']);
            foreach ($split as $col) {
                array_push($select, $table . '.' . trim($col));
            }
        }
        return $select;
    }

    public function formatSelectData($datas)
    {
        $res = [];
        foreach ($datas as $data) {
            array_push($res, $this->formatData($data));
        }
        return $res;
    }

    public function formatData($data)
    {
        if ($this->is_app) {
            return $this->formatDataApp($data);
        }
        return $this->formatDataCms($data);
    }

    public function formatDataCms($data)
    {
        $res = json_decode($data, true);
        if(isset($res['medias'])){
            $res['medias'] = json_decode($res['medias'], true);
        }
        return $res;
    }

    public function formatDataApp($data)
    {
        $res = json_decode($data, true);
        //        if(isset($res['created_at'])){
        //            $res['created_at'] = Carbon::parse($res['created_at'])->format('d/m/Y H:i:s');
        //        }
        //        if(isset($res['updated_at'])){
        //            $res['updated_at'] = Carbon::parse($res['updated_at'])->format('d/m/Y H:i:s');
        //        }

        return $res;
    }

    public function checkInputs($inputs, $id)
    {
        if (isset($inputs['is_app'])) {
            $this->is_app = $inputs['is_app'];
        }

        return [
            'is_failed' => false,
            'inputs' => $inputs
        ];
    }

    public function generateQuery($terms, $columns)
    {
        $sql_util = new SqlUtil();
        $search = null;

        if (is_string($terms) && !empty($terms)) {
            $search = trim($terms);
        } else if (is_array($terms) && count($terms) > 0) {
            foreach ($terms as $term) {
                if (!is_array($term)) {
                    if (!empty($term)) {
                        $search = trim($term);
                    }
                } else if (count($term) > 0) {
                    $conditions = [];
                    foreach ($term as $cond) {
                        if (in_array($cond['field'], $this->getQueryDateField())) {
                            $conditions[] = $sql_util->generateDateField($cond);
                        } else if (in_array($cond['field'], $this->getQueryField())) {
                            $conditions[] = $sql_util->generateNormalField($cond);
                        }
                    }
                    if (count($conditions) > 0) {
                        $columns[] = '(' . implode(' OR ', $conditions) . ')';
                    }
                }
            }
        }

        return [
            'columns' => $columns,
            'search' => $search ?? ""
        ];
    }

    public function getJoinTable()
    {
        return [];
    }

    public function getQueryDateField()
    {
        return [
            $this->getTableName() . '.created_at',
            $this->getTableName() . '.updated_at'
        ];
    }

    public function getQueryField()
    {
        return [
            $this->getTableName() . '.id',
            $this->getTableName() . '.reference'
        ];
    }

    public function searchElastic($inputs)
    {
        $inputs["limit"] = $inputs["limit"] ?? 1000;
        $inputs["search"] = $inputs["search"] ?? "";
        $isSelect = $inputs["is_select"] ?? 1;

        $search = $this->setSearchElastic($inputs);

        if ($isSelect === 2 || $isSelect === 3) {
            $dataRaw = $search->paginateRaw($inputs["limit"]);
            $datasArray = [];
            foreach ($dataRaw->items() as $i) {
                $datasArray = $datasArray + $i["hits"]["hits"];
            }

            $datasLookup = array_map(fn($item) => array_merge(
                $item["_source"],
                !empty ($item['sort']) ? ['sort' => $item['sort'][0]] : []
            ), $datasArray);

            if ($isSelect === 2) {
                $datas = $dataRaw->setCollection(collect($datasLookup));
            } else {
                $dataPaginate = $search->paginate($inputs["limit"]);
                $dataArrayMer = [];
                foreach ($dataPaginate->items() as $index => $paginateItem) {
                    $dataArrayMer[] = array_merge($paginateItem->getAttributes(), $datasLookup[$index]);
                }
                $datas = $dataPaginate->setCollection(collect($dataArrayMer));
            }
        } else {
            $dataPaginate = $search->paginate($inputs["limit"]);
            $datas = $dataPaginate;
        }

        return [
            "code" => "200",
            "data" => $datas
        ];
    }

    public function searchElasticCache($inputs) {
        $inputs["limit"] = $inputs["limit"] ?? 1000;
        $inputs["search"] = $inputs["search"] ?? "";
        $isSelect = $inputs["is_select"] ?? 1;

        $search = $this->setSearchElastic($inputs);

        $key = $inputs['key'] ?? 'product_';
        $datasArray = [];
        $dataRaw = $search->paginateRaw($inputs["limit"]);
        foreach ($dataRaw->items() as $i) {
            $datasArray = $datasArray + $i["hits"]["hits"];
        }

        if ($isSelect === 2 || $isSelect === 3) {
            $datasLookup = array_map(fn($item) => array_merge(
                $item["_source"],
                !empty($item['sort']) ? ['sort' => $item['sort'][0]] : []
            ), $datasArray);

            if ($isSelect === 2) {
                $datas = $dataRaw->setCollection(collect($datasLookup));
            }
            else {
                $dataArrayMer = [];
                $cache_keys = [];
                foreach($datasArray as $item) {
                    array_push($cache_keys, $key . $item['_id']);
                }
                $cache_keys = Cache::many($cache_keys);

                foreach($datasLookup as $item) {
                    $dat = isset($cache_keys[$key . $item['id']]) ? $cache_keys[$key . $item['id']] : null;
                    if(isset($dat)) {
                        $dataArrayMer[] = array_merge(json_decode($dat, true), $item);
                    }

                }
                $datas = $dataRaw->setCollection(collect($dataArrayMer));
            }
        } else {
            $dataCache = [];
            $cache_keys = [];
            foreach($datasArray as $item) {
                array_push($cache_keys, $key . $item['_id']);
            }
            $cache_keys = Cache::many($cache_keys);
            foreach($cache_keys as $k=>$cache) {
                array_push($dataCache, $cache);
            }

            $datas = $dataRaw->setCollection(collect($dataCache));
        }

        return [
            "code" => "200",
            "data" => $datas
        ];
    }

    private function setSearchElastic($inputs) {
        $inputs["limit"] = $inputs["limit"] ?? 1000;
        $inputs["search"] = $inputs["search"] ?? "";
        $isSelect = $inputs["is_select"] ?? 1;

        $search = $this->repo_base->getModel()::search($inputs["search"]);
        // ->take(1000)->get(); đoạn này chỉ để tự phân trang, không hoạt động với paginate

        if (isset($inputs["must"])) {
            foreach ($inputs["must"] as $item) {
                $match = new Matching($item["field"], $item["value"], $item["fuzziness"] ?? "AUTO");

                if (isset($item["analyzer"]))
                    $match->setAnalyzer($item["analyzer"]);

                $search = $search->must($match);
            }
        }

        if (isset($inputs["match_phrase"])) {
            $match_phrase = $inputs["match_phrase"];
            $search = $search->must(new MatchPhrase($match_phrase['field'], $match_phrase['value']));
        }

        if (isset($inputs["match_all"])) {
            $search = $search->must(new MatchAll());
        }

        if (isset($inputs["multi_match"])) {
            $match = $inputs["multi_match"];
            $search = $search->must(new MultiMatch($match["value"], $match["fields"], $match["fuzziness"] ?? "AUTO", $match["prefix_length"] ?? 0));
        }

        if (isset($inputs["terms"])) {
            foreach ($inputs["terms"] as $term) {
                $search = $search->should(new Terms($term["field"], $term["values"], $term["boost"] ?? 1));
            }
        }

        if (isset($inputs["query_string"])) {
            foreach ($inputs["query_string"] as $each) {
                $QueryString = new QueryString(
                    $each["query"],
                    $each["default_operator"] ?? QueryString::OP_OR,
                    $each["boost"] ?? 1
                );
                $search = $search->should($QueryString);
            }
        }
        if (isset($inputs["filter"])) {
            $filter = $inputs["filter"];
            $search = $search->filter(new Term($filter["field"], $filter["value"], $filter["boost"] ?? 1));
        }

        if(isset($inputs["ranges"])) {
            foreach ($inputs["ranges"] as $range)
                $search = $search->filter(new Range($range["field"], $range["option"]));
        }

        if(isset($inputs["prefix"])) {
            foreach ($inputs["prefix"] as $item)
                $search = $search->filter(new Prefix($item["field"], $item["value"]));
        }

        if (isset($inputs["geo_distance"])) {
            $geo = $inputs["geo_distance"];
            $search = $search->filter(new GeoDistance(
                $geo["distance"],
                $geo["lat"],
                $geo["lng"],
                $geo["distance_type"] ?? GeoDistance::DISTANCE_TYPE_ARC,
                $geo["field"] ?? GeoDistance::DEFAULT_FIELD
            ));
        }

        if (isset($inputs["sort"])) {
            $sort = $inputs["sort"];
            $search = $search->property(new QuerySort($sort));
        }

        if(isset($inputs["must_not"])) {
            $array = [];
            foreach ($inputs["must_not"] as $item) {
                $array[] = new Matching($item["field"], $item["value"]);
            }
            $boolQuery = new MustNot();
            $boolQuery->addMany("must_not", $array);
            $search = $search->newCompound($boolQuery );
        }
        return $search;
    }
}
