<?php

namespace App\Models;

use Elasticsearch\ClientBuilder;

class FilterElasticsearch
{
    protected $client;

    public function __construct(ClientBuilder $client)
    {
        $this->client = $client;
    }
    // Định nghĩa index mà model này sẽ sử dụng trong Elasticsearch
    public function searchableAs(): string
    {
        return 'filter_index';
    }

    // Định nghĩa dữ liệu mà bạn muốn lưu vào Elasticsearch
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'sub_category_id' => isset($this->sub_category_id) ? json_encode($this->sub_category_id) : null,
            'category_id' => isset($this->category_id) ? json_encode($this->category_id) : null,
            'user_id' => $this->user_id ?? null,
            'post_industry_id' => isset($this->post_industry_id) ? json_encode($this->post_industry_id) : null,
            'keyword' => $this->keyword ?? null,
            "location" =>isset($this->location) ? json_decode($this->location, true) : null,
            "nearby_areas" => isset($this->nearby_areas) ? json_decode($this->nearby_areas, true) : null,
            'utilities' => isset($this->utilities) ? json_decode($this->utilities, true) : null,
            'num_employees' => isset($this->num_employees) ? json_decode($this->num_employees, true) : null,
            'avg_revenue' => isset($this->avg_revenue) ? json_decode($this->avg_revenue, true) : null,
            'lease_remaining' => isset($this->lease_remaining) ? json_decode($this->lease_remaining, true) : null,
            'money_rent' => isset($this->money_rent) ? json_decode($this->money_rent, true) : null,
            'num_chairs' => isset($this->num_chairs) ? json_decode($this->num_chairs, true) : null,
            'num_tables' => isset($this->num_tables) ? json_decode($this->num_tables, true) : null,
            'price' => isset($this->price) ? json_decode($this->price, true) : null,
        ];
    }

    public function mappableAs(): array
    {
        return [
            'id' => 'keyword',
            'sub_category_id' => 'keyword',
            'category_id' => 'keyword',
            'user_id' => 'keyword',
            'post_industry_id' => [
                'type' => 'text',
            ],
            'keyword' => [
                'type' => 'text',
            ],
            "location" => [
                'type' => 'geo_point',
            ],
            "nearby_areas" => [
                'type' => 'keyword',
            ],
            'lease_agreement' => [
                'type' => 'object',
                'properties' => [
                    'money_rent' => ['type' => 'float'],
                    'lease_remaining' => ['type' => 'integer'],
                    'more_info' => ['type' => 'text'],
                ]
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
        ];
    }

    public function indexSettings(): array
    {
        return [

        ];
    }
}
