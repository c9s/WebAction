<?php

namespace WebAction\Maghead;

use Maghead\Testing\ModelTestCase;

use OrderBundle\Model\OrderSchema;
use OrderBundle\Model\OrderItemSchema;
use OrderBundle\Model\Order;
use OrderBundle\Model\OrderItem;

use ProductBundle\Model\ProductSchema;
use ProductBundle\Model\Product;
use ProductBundle\Model\Category;
use ProductBundle\Model\CategorySchema;
use ProductBundle\Action\CreateProduct;

use Maghead\Schema\RuntimeColumn;
use Magsql\Raw;

use WebAction\View\StackView;
use WebAction\Action;
use WebAction\Param\Param;
use WebAction\RecordAction\BaseRecordAction;
use DateTime;
use Closure;

class CreateOrder extends \WebAction\RecordAction\CreateRecordAction
{
    public $recordClass = Order::class;
}

class ColumnConvertTest extends ModelTestCase
{
    public function models()
    {
        return [new OrderSchema, new OrderItemSchema, new ProductSchema, new CategorySchema];
    }

    public function testColumnNotNullWithDefaultShouldNotBeRequiredField()
    {

    }

    public function testConvertDateTimeDefaultClosure()
    {
        $schema = Order::getSchema();
        $column = $schema->getColumn('created_at');
        $this->assertInstanceOf(RuntimeColumn::class, $column);

        // assert the input
        $this->assertInstanceOf(Closure::class, $column->default);

        $param = ColumnConvert::toParam($column, new CreateOrder);
        $this->assertInstanceOf(Param::class, $param);
        $this->assertInstanceOf(DateTime::class, $param->getDefaultValue()); 
    }

    public function testConvertColumnNotNull()
    {
        $schema = Order::getSchema();
        $column = $schema->getColumn('amount');
        $this->assertInstanceOf(RuntimeColumn::class, $column);

        $param = ColumnConvert::toParam($column, new CreateOrder);
        $this->assertInstanceOf(Param::class, $param);

        $this->assertTrue($param->required);
    }

    public function testConvertCurrentTimestampIntoPHPDateTimeObject()
    {
        $schema = Order::getSchema();
        $column = $schema->getColumn('updated_at');
        $this->assertInstanceOf(RuntimeColumn::class, $column);

        // assert the input
        $this->assertInstanceOf(Raw::class, $column->default);
        $this->assertEquals('CURRENT_TIMESTAMP', $column->default->__toString());

        $param = ColumnConvert::toParam($column, new CreateOrder);
        $this->assertInstanceOf(Param::class, $param);
        $this->assertEquals('DateTime', $param->isa);
        $this->assertNull($param->getDefaultValue()); 
    }

    public function testConvertReferIntoValidValues()
    {
        $ret = Category::create([ 'name' => 'A' ]);
        $this->assertResultSuccess($ret);

        $ret = Category::create([ 'name' => 'B' ]);
        $this->assertResultSuccess($ret);


        $schema = Product::getSchema();
        $column = $schema->getColumn('category_id');

        $this->assertNotNull($column->refer, 'has refer');

        $param = ColumnConvert::toParam($column, new CreateProduct);
        $this->assertInstanceOf(Param::class, $param);

        $this->assertNotEmpty($validValues = $param->getValidValues());
        $this->assertEquals([
            ['label' => 'A', 'value' => 1],
            ['label' => 'B', 'value' => 2],
        ], $validValues);
    }



    public function testColumnConvert()
    {
        $schema = Order::getSchema();
        $this->assertNotNull($schema);

        $order = new Order;
        $action = ColumnConvert::convertSchemaToAction($schema, $order);
        $this->assertNotNull($action);
        $this->assertInstanceOf(Action::class, $action);
        $this->assertInstanceOf(BaseRecordAction::class, $action);

        $view = $action->asView(StackView::class);
        $this->assertNotNull($view);
        $this->assertInstanceOf(StackView::class, $view);
    }
}
