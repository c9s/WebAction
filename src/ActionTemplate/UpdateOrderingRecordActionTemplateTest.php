<?php
use WebAction\Testing\ActionTestCase;
use WebAction\ActionTemplate\UpdateOrderingRecordActionTemplate;
use WebAction\ActionRunner;
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
        $runner = new ActionRunner;
        $actionTemplate->register($runner, 'UpdateOrderingRecordActionTemplate', $arguments);
    }


    public function testGenerationWithRecordClassOption()
    {
        $recordClass = 'OrderingTest\Model\Foo';
        $className = 'OrderingTest\Action\UpdateFooOrdering';

        $actionTemplate = new UpdateOrderingRecordActionTemplate;
        $runner = new ActionRunner;
        $actionTemplate->register($runner, 'UpdateOrderingRecordActionTemplate', array(
            'record_class' => $recordClass,
        ));
        $this->assertCount(1, $runner->getPretreatments());
        $this->assertNotNull($pretreatment = $runner->getActionPretreatment($className));
        $generatedAction = $actionTemplate->generate($className, $pretreatment);
        $this->assertRequireGeneratedAction($className, $generatedAction);
    }


    public function testUpdateOrderingRecordActionTemplate()
    {
        $actionTemplate = new UpdateOrderingRecordActionTemplate;
        $runner = new ActionRunner;
        $actionTemplate->register($runner, 'UpdateOrderingRecordActionTemplate', array(
            'namespace' => 'OrderingTest',
            'model' => 'Test2Model'   // model's name
        ));

        $className = 'OrderingTest\Action\UpdateTest2ModelOrdering';

        $this->assertCount(1, $runner->getPretreatments());
        $this->assertNotNull($pretreatment = $runner->getActionPretreatment($className));

        $generatedAction = $actionTemplate->generate($className, $pretreatment);
        $this->assertRequireGeneratedAction($className, $generatedAction);
    }
}

