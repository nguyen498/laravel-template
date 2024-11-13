<?php

namespace App\Lib\Models;

use JeroenG\Explorer\Domain\Syntax\Compound\BoolQuery;

class MustNotTerms extends BoolQuery
{
    public function __construct(array $terms)
    {
        parent::__construct();

        // Tạo truy vấn must_not với các term.
        $this->mustNot($terms);
    }

    public function mustNot(array $terms)
    {
        // Tạo truy vấn 'must_not' cho mỗi term trong mảng.
        $mustNotConditions = [];
        foreach ($terms as $field => $value) {
            $mustNotConditions[] = [
                'term' => [
                    $field => $value
                ]
            ];
        }

        // Thêm điều kiện 'must_not' vào BoolQuery
        $this->query['bool']['must_not'] = $mustNotConditions;

        return $this;
    }
}
