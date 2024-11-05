<?php

declare(strict_types=1);

namespace App\Lib\Models;

use JeroenG\Explorer\Domain\Syntax\SyntaxInterface;

class Prefix implements SyntaxInterface
{
    protected string $field;
    protected string $value;
    protected ?string $analyzer; 

    public function __construct(string $field, string $value, ?string $analyzer = null) 
    {
        $this->field = $field;
        $this->value = $value;
        $this->analyzer = $analyzer;
    }

    public function build(): array
    {
        $match = [
            $this->field => [
                'query' => $this->value,
            ],
        ];

        if ($this->analyzer !== null) {
            $match[$this->field]['analyzer'] = $this->analyzer;
        }

        return [
            'match' => $match,
        ];
    }
}
