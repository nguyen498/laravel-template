<?php

declare(strict_types=1);

namespace App\Lib\Models;

use Illuminate\Support\Collection;
use JeroenG\Explorer\Domain\Syntax\Compound\BoolQuery;
use JeroenG\Explorer\Domain\Syntax\SyntaxInterface;

class MustNot extends BoolQuery
{
    private Collection $mustNot;

    public function __construct()
    {
        parent::__construct(); 
        $this->mustNot = new Collection(); 
    }

    public function mustNot(SyntaxInterface $syntax): void
    {
        $this->mustNot->add($syntax);
    }

    public function build(): array
    {
        $boolQuery = parent::build()['bool'];

        $boolQuery['must_not'] = $this->mustNot->map(fn ($mustNot) => $mustNot->build())->toArray();

        return [
            'bool' => $boolQuery
        ];
    }

    public function clone(): self
    {
        $query = new MustNot();
        $query->mustNot = clone $this->mustNot;
        return $query;
    }
}

