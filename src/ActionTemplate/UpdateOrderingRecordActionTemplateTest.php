<?php

use WebAction\Testing\ActionTestCase;
use WebAction\ActionTemplate\UpdateOrderingRecordActionTemplate;
use WebAction\ActionRunner;
use WebAction\ActionLoader;
use WebAction\ActionGenerator;
use WebAction\GeneratedAction;

/**
 * @group maghead
 */
class UpdateOrderingRecordActionTemplateTest extends ActionTestCase
{
    public function failingArgumentProvider()
    {
        return [ 
            [ [
                'use' => ['OrderingTest\SomeProvider']
            ] ],
            [ [
                'namespace' => 'OrderingTest',
            ] ],
            [ [
                'model' => 'Bar',   // model's name
            ] ],
        ];
    }


    /**
     * @dataProvider failingArgumentProvider
     * @expectedException WebAction\Exception\RequiredConfigKeyException
     */
    public function testUpdateOrderingRecordActionTemplateWithFailingArguments($arguments)
    {
        $recordClass = 'OrderingTest\Model\Foo';
        $className = 'OrderingTest\Action\UpdateFooOrdering';

        $actionTemplate = new UpdateOrderingRecordActionTemplate;
        $loader = new ActionLoader(new ActionGenerator);
        $actionTemplate->register($loader, 'UpdateOrderingRecordActionTemplate', $arguments);
    }


    public function testGenerationWithRecordClassOption()
    {
        $recordClass = 'OrderingTest\Model\Foo';
        $className = 'OrderingTest\Action\UpdateFooOrdering';

        $loader = new ActionLoader(new ActionGenerator);

        $actionTemplate = new UpdateOrderingRecordActionTemplate;
        $actionTemplate->register($loader, 'UpdateOrderingRecordActionTemplate', array(
            'record_class' => $recordClass,
        ));
        $this->assertCount(1, $loader->getPretreatments());
        $this->assertNotNull($pretreatment = $loader->getPretreatment($className));
        $generatedAction = $actionTemplate->generate($className, $pretreatment);
        $this->assertRequireGeneratedAction($className, $generatedAction);
    }


    public function testUpdateOrderingRecordActionTemplate()
    {
        $actionTemplate = new UpdateOrderingRecordActionTemplate;
        $loader = new ActionLoader(new ActionGenerator);
        $actionTemplate->register($loader, 'UpdateOrderingRecordActionTemplate', array(
            'namespace' => 'OrderingTest',
            'model' => 'Test2Model'   // model's name
        ));

        $className = 'OrderingTest\Action\UpdateTest2ModelOrdering';

        $this->assertCount(1, $loader->getPretreatments());
        $this->assertNotNull($pretreatment = $loader->getPretreatment($className));

        $generatedAction = $actionTemplate->generate($className, $pretreatment);
        $this->assertRequireGeneratedAction($className, $generatedAction);
    }
}

