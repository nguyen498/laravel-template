<?php

declare(strict_types=1);

namespace App\Lib\Models;

use JeroenG\Explorer\Domain\Query\QueryProperties\QueryProperty;
use JeroenG\Explorer\Domain\Syntax\SyntaxInterface;


class QuerySort implements QueryProperty
{
	const ASC = 'asc';
	const DESC = 'desc';
	const DEFAULT_FIELD = 'pin.location';
	const DEFAULT_UNIT = 'km';
	const DISTANCE_TYPE_ARC = 'arc';
	const DISTANCE_TYPE_PLANE = 'plane';
	private array $query;

	public function __construct(array $query)
	{
		$this->query = $query;
	}

	public function build(): array
	{
		$sort = [];
		foreach ($this->query as  $order) {
			if(isset($order['_geo_distance'])){
				$_geo_distance = $order['_geo_distance'];
				$field = $_geo_distance['field'] ?? self::DEFAULT_FIELD;
				$sort[] = [
					'_geo_distance' => [
						$field => [
							'lat' => $_geo_distance['lat'],
							'lon' => $_geo_distance['lon']
						],
						'order' => $_geo_distance['order'] ?? self::DESC,
						'unit' => $_geo_distance['unit'] ?? self::DEFAULT_UNIT,
						'distance_type' => $_geo_distance['distance_type'] ?? self::DISTANCE_TYPE_ARC
					]
				];
			}
			else{
				$field = key($order);
				$sort[] = [
					$field =>$order[$field]
				];
			}
		}

		return [
			"sort" => $sort
		];
	}
}
