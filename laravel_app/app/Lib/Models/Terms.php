<?php

namespace App\Lib\Models;

use JeroenG\Explorer\Domain\Syntax\SyntaxInterface;
use Webmozart\Assert\Assert;

class Terms implements SyntaxInterface
{
    private string $field;

    private array $values;

    private ?float $boost;

    public function __construct(string $field, array $values = [], ?float $boost = 1.0)
    {
        // Ép kiểu tất cả các phần tử trong `$values` thành chuỗi
        $this->values = array_map('strval', $values);

        Assert::allStringNotEmpty($this->values);

        $this->field = $field;
        $this->boost = $boost;
    }

    public function build(): array
    {
        return ['terms' => [
            $this->field => $this->values,
            'boost' => $this->boost,
        ]];
    }
}
