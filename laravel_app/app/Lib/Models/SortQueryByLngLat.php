<?php

declare(strict_types=1);

namespace App\Lib\Models;

use JeroenG\Explorer\Domain\Query\QueryProperties\QueryProperty;
use JeroenG\Explorer\Domain\Syntax\SyntaxInterface;


class SortQueryByLngLat implements QueryProperty
{
	const ASC = 'asc';
	const DESC = 'desc';
	const DEFAULT_FIELD = 'pin.location';
	const DEFAULT_UNIT = 'km';
	const DISTANCE_TYPE_ARC = 'arc';
	const DISTANCE_TYPE_PLANE = 'plane';

	private mixed $lat;
	private mixed $lng;
	private mixed $order;
	private ?string $distance_type = 'arc';
	private string $field;
	private mixed $unit;
    private mixed $order_by;

	public function __construct($lat, $lng, $order = self::ASC, $field = self::DEFAULT_FIELD, $unit = self::DEFAULT_UNIT, $distance_type = self::DISTANCE_TYPE_ARC, $order_by = [])
	{
		$this->lat = $lat;
		$this->lng = $lng;
		$this->field = $field;
		$this->order = $order;
		$this->unit = $unit;
		$this->distance_type = $distance_type;
		$this->order_by = $order_by;
	}

	public function build(): array
	{
	    $sort = [];
	    if($this->lat != 0) {
            $sort = [
                "_geo_distance" => [
                    $this->field => [
                        "lat" => (string) $this->lat,
                        "lon" => (string) $this->lng
                    ],
                    "order" => (string) $this->order,
                    "unit" => (string) $this->unit,
                    "distance_type" => (string) $this->distance_type
                ]
            ];
        }
	    if(count($this->order_by) > 0) {
	       $sort[$this->order_by['field']] = $this->order_by['order'];
        }
		return [
			"sort" => $sort
		];
	}
}
