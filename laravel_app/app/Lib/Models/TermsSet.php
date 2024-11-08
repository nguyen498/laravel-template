<?php

namespace App\Lib\Models;

use JeroenG\Explorer\Domain\Syntax\SyntaxInterface;
use JeroenG\Explorer\Domain\Syntax\Term;

class TermsSet implements SyntaxInterface
{
    protected $field;
    protected $values;

    /**
     * TermsSet constructor.
     *
     * @param string $field Tên trường muốn tìm kiếm.
     * @param array $values Mảng giá trị cần có trong trường.
     */
    public function __construct(string $field, array $values)
    {
        $this->field = $field;
        $this->values = $values;
    }

    /**
     * Xây dựng cú pháp cho truy vấn Terms Set.
     *
     * @return array Cú pháp cho Elasticsearch query.
     */
    public function build(): array
    {
        return [
            'terms_set' => [
                $this->field => [
                    'terms' => $this->values,
                    'minimum_should_match_script' => [
                        "source" => (string)count($this->values)
                    ], // Đảm bảo tất cả các giá trị có mặt
                ]
            ]
        ];
    }
}

