<?php

namespace App\Lib\Models;

use JeroenG\Explorer\Domain\Query\QueryProperties\QueryProperty;

class SortRandom implements QueryProperty
{

    public function build(): array
    {
        return [
            "sort" => [
                "_script" => [
                    "type" => 'number',
                    "script" => [
                        "source" => 'Math.random()'
                    ],
                    "order" => 'asc'
                ]
            ]
        ];
    }
}
