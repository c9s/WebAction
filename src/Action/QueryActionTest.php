<?php

namespace WebAction\Action;

use WebAction\Action;
use WebAction\Utils;
use WebAction\Exception\InvalidActionNameException;

use Maghead\Testing\ModelTestCase;
use ProductBundle\Model\ProductSchema;
use ProductBundle\Model\CategorySchema;

class QueryActionTest extends ModelTestCase
{

    public function models()
    {
        return [new ProductSchema, new CategorySchema];
    }

    public function testQueryValidValuesShouldReturnTheListOfValues()
    {
        $a = new QueryAction([
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
            '可販售' => 1,
            '無法販售' => 0,
        ], $result->data['validValues']);
    }
}

