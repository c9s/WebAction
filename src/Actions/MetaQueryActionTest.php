<?php

namespace WebAction\Actions;

use WebAction\Action;
use WebAction\Utils;
use WebAction\Exception\InvalidActionNameException;

use Maghead\Testing\ModelTestCase;
use ProductBundle\Model\ProductSchema;
use ProductBundle\Model\CategorySchema;
use ProductBundle\Model\Category;

class MetaQueryActionTest extends ModelTestCase
{

    public function models()
    {
        return [new ProductSchema, new CategorySchema];
    }

    public function testQueryValidValuesOfSellableFieldShouldReturnTheListOfValues()
    {
        $a = new MetaQueryAction([
            'action' => 'ProductBundle::Action::CreateProduct',
            'field' => 'sellable',
            'attribute' => 'validValues',
        ]);
        $ret = $a->handle();
        $this->assertTrue($ret);

        $result = $a->getResult();
        $data = $result->data;

        $this->assertArrayHasKey('validValues', $data);
        $this->assertEquals([
            ["label" => "可販售",   "value" => 1],
            ["label" => "無法販售", "value" => 0],
        ], $result->data['validValues']);
    }

    public function testQueryValidValuesOfCategoryIdFieldShouldReturnTheListOfValues()
    {
        $ret = Category::create([ 'name' => 'A' ]);
        $this->assertResultSuccess($ret);

        $ret = Category::create([ 'name' => 'B' ]);
        $this->assertResultSuccess($ret);

        $a = new MetaQueryAction([
            'action'    => 'ProductBundle::Action::CreateProduct',
            'field'     => 'category_id',
            'attribute' => 'validValues',
        ]);

        $ret = $a->handle();
        $this->assertTrue($ret);

        $result = $a->getResult();
        $data = $result->data;

        $this->assertArrayHasKey('validValues', $data);
        $this->assertEquals([
            ['label' => 'A', 'value' => 1],
            ['label' => 'B', 'value' => 2],
        ], $result->data['validValues']);
    }
}
