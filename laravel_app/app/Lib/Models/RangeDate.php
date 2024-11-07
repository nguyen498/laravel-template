<?php

namespace App\Lib\Models;

use JeroenG\Explorer\Domain\Syntax\SyntaxInterface;

class RangeDate implements SyntaxInterface
{
    private string $field;

    private mixed $option;

    public function __construct(string $field, $option)
    {
        $this->field = $field;
        $this->option = $option;
    }

    public function build(): array
    {
        return ['range' => [
            $this->field => $this->option,
        ]];
    }
}
