<?php

declare(strict_types=1);

namespace App\Lib\Models;

use JeroenG\Explorer\Domain\Query\QueryProperties\QueryProperty;


class MatchPhrase implements QueryProperty
{
	protected array $match_phrase;
	public function __construct(array $match_phrase)
	{
		$this->match_phrase = $match_phrase;
	}

	public function build(): array
	{
		$match_phrase_array = [];
		foreach ($this->match_phrase as $value) {
			$value = $value + ["fuzziness" => "AUTO"];
		}
		return [
			"match_phrase" => $match_phrase_array
		];
	}
}
