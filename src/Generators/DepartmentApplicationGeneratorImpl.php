<?php


namespace Js3\ApprovalFlow\Generators;


use Js3\ApprovalFlow\Entity\AuthInfo;

/**
 * @explain: 仅用作示例
 * @author: wzm
 * @date: 2024/5/17 15:58
 */
class DepartmentApplicationGeneratorImpl implements RelateApplicationGenerator
{

    private $ary_demo = [
        [
            "name" => "name1",
            "value" => "value1",
            "children" => [
                "name" => "name1-1",
                "value" => "value1-1",
                "children" => []
            ],
        ],
        [
            "name" => "name2",
            "value" => "value2",
        ],
    ];
    /**
     * @inheritDoc
     */
    public function options(AuthInfo $authInfo): array
    {
        return $this->ary_demo;

    }

    /**
     * @inheritDoc
     */
    public function children(AuthInfo $authInfo, $parent_slug)
    {
        $return_data = [];
        foreach ($this->ary_demo as $demo) {
            if ($demo["name"] == $parent_slug) {
                $return_data = $demo;
                break;
            }
        }
        return $return_data;
    }
}