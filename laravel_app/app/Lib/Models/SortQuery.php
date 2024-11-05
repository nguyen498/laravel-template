<?php

declare(strict_types=1);

namespace App\Lib\Models;

use JeroenG\Explorer\Domain\Query\QueryProperties\QueryProperty;


class SortQuery implements QueryProperty
{
	const ASC = 'asc';
	const DESC = 'desc';
	const DEFAULT_FIELD = 'pin.location';

	private mixed $order;
	private string $field;

	public function __construct($field = self::DEFAULT_FIELD, $order = self::ASC)
	{
		$this->field = $field;
		$this->order = $order;
	}

	public function build(): array
	{
		return [
			"sort" => [ $this->field => $this->order ]
		];
	}
}
