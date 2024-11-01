<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/30/21
 * Time: 1:02 PM
 */

namespace App\Services\Base;


use App\Utils\LogHelper;
use Elasticsearch\ClientBuilder;
use Elasticsearch;

class ElasticService
{
    public function getIndex($inputs) {
        $data = [
            'body' => [
                "track_total_hits" => true
            ],
            'index' => $inputs['index'],
        ];

        $client = ClientBuilder::create()->build();
        return $this->callingElastic($client->index($data));
    }

    public function search($inputs) {
        $params = [
            'index' => $inputs['index']
        ];
        $body = [];
        if(isset($inputs['limit'])) {
            $body['from'] = ($inputs['page'] - 1) * $inputs['limit'];
            $body['size'] = $inputs['limit'];
        }
        if(isset($inputs['sort'])) {
            $body['sort'] = $inputs['sort'];
        }
        if(isset($inputs['query'])) {
            $body['query'] = $inputs['query'];
        }
        if(isset($inputs['aggs'])) {
            $body['aggs'] = $inputs['aggs'];
        }

        if(isset($inputs['autocompleteQuery'])) {
            $body['autocompleteQuery'] = $inputs['autocompleteQuery'];
        }

        if(isset($inputs['suggest'])) {
            $body['suggest'] = $inputs['suggest'];
        }

        if(isset($body)) {
            $params['body'] = $body;
        }
        LogHelper::writeLog('param search ' . json_encode($params), 1);

        return $this->callingElastic(Elasticsearch::search($params));

//        $milliseconds = $results['took'];
//        $maxScore     = $results['hits']['max_score'];
//
//        $score = $results['hits']['hits'][0]['_score'];
//        $doc   = $results['hits']['hits'][0]['_source'];
    }



    public function searchAggregation($inputs) {
        $params = [
            'index' => $inputs['index']
        ];
        $body = [];
        if(isset($inputs['limit'])) {
            $body['size'] = $inputs['limit'];
        }
        if(isset($inputs['query'])) {
            $body['query'] = $inputs['query'];
        }
        if(isset($inputs['aggs'])) {
            $body['aggs'] = $inputs['aggs'];
        }
        if(isset($body)) {
            $params['body'] = $body;
        }
//        LogHelper::writeLog('param search aggregation ' . json_encode($params), 1);
        return $this->callingElastic(Elasticsearch::search($params));
    }

    public function searchAll($inputs) {
        $params = [
            'index' => $inputs['index'],
            'body'  => [
                'from' => ($inputs['page'] - 1) * $inputs['limit'], // page
                'size' => $inputs['limit'], // limit,
                'query' => [
                    'match_all' => new \stdClass()
                ]
            ]
        ];

        return $this->callingElastic(Elasticsearch::search($params));
    }
    // can use for both create and update
    public function create($inputs) {
        $params = [
            'index' => $inputs['index'],
            'id'    => $inputs['id'],
            'body'  => $inputs['data']
        ];
        return $this->callingElastic(Elasticsearch::index($params));
    }
    // insert many
    public function bulk($params) {
        return $this->callingElastic(Elasticsearch::bulk($params));
    }

    public function findById($inputs) {
        $params = [
            'index' => $inputs['index'],
            'id'    => $inputs['id'],
        ];
        return $this->callingElastic(Elasticsearch::get($params));
    }

    public function update($inputs) {
        $params = [
            'index' => $inputs['index'],
            'id'    => $inputs['id'],
            'body'  => [
                'doc' => $inputs['data']
            ]
        ];
        return $this->callingElastic(Elasticsearch::update($params));
    }

    public function delete($inputs) {
        $params = [
            'index' => $inputs['index'],
            'body' => [
                'query' => [
                    'match' => [
                        '_id' => $inputs['id']
                    ]
                ]
            ]
        ];

        return $this->callingElastic(Elasticsearch::deleteByQuery($params));
    }

    public function deleteByQuery($inputs) {
        $params = [
            'index' => $inputs['index'],
            'body' => $inputs['body']
        ];

        return $this->callingElastic(Elasticsearch::deleteByQuery($params));
    }

    public function createIndex($inputs) {
        $params = [
            'index' => $inputs['index'],
            'body' => $inputs['body']
        ];
        return $this->callingElastic(Elasticsearch::indices()->create($params));
    }

    public function deleteIndex($inputs) {
        $params = [
            'index' => $inputs['index']
        ];
        return $this->callingElastic(Elasticsearch::indices()->delete($params));
    }

    public function getSetting($inputs) {
        $params = [
            'index' => $inputs['index']
        ];
        return $this->callingElastic(Elasticsearch::indices()->getSettings($params));
    }

    public function putMapping($inputs) {
        $params = [
            'index' => $inputs['index'],
            'body' => $inputs['body']
        ];
        return $this->callingElastic(Elasticsearch::indices()->putMapping($params));
    }

    public function getMapping($inputs) {
        $params = [
            'index' => $inputs['index']
        ];
        return $this->callingElastic(Elasticsearch::indices()->getMapping($params));
    }

    public function updateByQuery($inputs) {
        $params = [
            'index' => $inputs['index'],
            'body' => $inputs['data']
        ];
        return $this->callingElastic(Elasticsearch::updateByQuery($params));
    }

    private function callingElastic($method) {
        try {
            return [
                'code' => '200',
                'data' => $method
            ];
        } catch(\Exception $e) {
            LogHelper::writeLog('error from elastic search ' . $e->getMessage());
            return [
                'code' => '090',
                'message' => $e->getMessage()
            ];
        }
    }
}
