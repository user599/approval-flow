<?php

namespace Js3\ApprovalFlow\Test\Entity;

use Js3\ApprovalFlow\Entity\SelectOptions;
use PHPUnit\Framework\TestCase;

class SelectOptionsTest extends TestCase
{
    private $test_array =  [
        [
            "id" => "1",
            "name" => "11",
            "parent_id" => 0,
            "desc" => "简述",
        ],
        [
            "id" => "2",
            "name" => "22",
            "parent_id" => 0,
            "desc" => "简述",
        ],
        [
            "id" => "3",
            "name" => "33",
            "parent_id" => 1,
            "desc" => "简述",
        ],
        [
            "id" => "4",
            "name" => "44",
            "parent_id" => 3,
            "desc" => "简述",
        ],
        [
            "id" => "5",
            "name" => "55",
            "parent_id" => 1,
            "desc" => "简述",
        ],
        [
            "id" => "6",
            "name" => "66",
            "parent_id" => 2,
            "desc" => "简述",
        ],


    ];
    public function testMakeOptions() {
        $selectOptions = SelectOptions::makeOptions($this->test_array, 'name', 'id');

    }
    public function testMakeChildren()
    {
        $makeChildren = SelectOptions::makeChildren($this->test_array, 'name', 'id', 'parent_id', 'id');
        dd($makeChildren);
    }
}
