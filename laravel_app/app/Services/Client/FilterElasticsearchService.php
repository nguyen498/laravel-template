<?php

namespace App\Services\Client;

use Elasticsearch\ClientBuilder;

class FilterElasticsearchService
{
    protected $client;

    public function __construct()
    {
        // Khởi tạo Elasticsearch client
        $this->client = ClientBuilder::create()
            ->setHosts(['http://elasticsearch:9200'])
            ->build();
    }

    // Định nghĩa tên index
    protected function getIndexName(): string
    {
        return 'filter_index';
    }

    public function indexExists(): bool
    {
        $params = [
            'index' => $this->getIndexName()
        ];

        $response = $this->client->indices()->exists($params);
        return $response;
    }

    // Tạo index nếu chưa tồn tại
    public function createIndex()
    {
        $params = [
            'index' => $this->getIndexName(),
            'body' => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0
                ],
                'mappings' => [
                    'properties' => [
                        'id' => ['type' => 'keyword'],
                        'sub_category_id' => ['type' => 'keyword'],
                        'category_id' => ['type' => 'keyword'],  // Đảm bảo khai báo chính xác kiểu
                        'user_id' => ['type' => 'keyword'],
                        'post_industry_id' => [
                            'type' => 'text',
                        ],
                        'keyword' => [
                            'type' => 'text',
                        ],
                        'location' => [
                            'type' => 'geo_point',
                        ],
                        'nearby_areas' => [
                            'type' => 'keyword',
                        ],
                        'utilities' => [
                            'type' => 'keyword',
                        ],
                        'num_employees' => [
                            'type' => 'integer_range',
                        ],
                        'avg_revenue' => [
                            'type' => 'integer_range',
                        ],
                        'lease_remaining' => [
                            'type' => 'integer_range',
                        ],
                        'money_rent' => [
                            'type' => 'integer_range',
                        ],
                        'num_chairs' => [
                            'type' => 'integer_range',
                        ],
                        'num_tables' => [
                            'type' => 'integer_range',
                        ],
                        'price' => [
                            'type' => 'integer_range',
                        ],
                    ]
                ]
            ]
        ];

        return $this->client->indices()->create($params);
    }

    // Phương thức để thêm dữ liệu vào Elasticsearch
    public function addToElasticsearch(array $data)
    {
        // Kiểm tra xem index đã tồn tại chưa
        if (!$this->indexExists()) {
            // Tạo index nếu chưa tồn tại
            $this->createIndex();
        }

        // Thêm document vào index
        $params = [
            'index' => $this->getIndexName(),
            'id'    => $data['id'],
            'body'  => $data,
        ];

        return $this->client->index($params);
    }
}
