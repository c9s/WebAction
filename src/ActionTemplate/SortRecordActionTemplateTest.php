<?php

use WebAction\Testing\ActionTestCase;
use WebAction\ActionTemplate\SortRecordActionTemplate;
use WebAction\ActionRunner;
use WebAction\ActionLoader;
use WebAction\ActionGenerator;
use WebAction\GeneratedAction;

/**
 * @group maghead
 */
class SortRecordActionTemplateTest extends ActionTestCase
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
    public function testSortRecordActionTemplateWithFailingArguments($arguments)
    {
        $recordClass = 'OrderingTest\Model\Foo';
        $className = 'OrderingTest\Action\UpdateFooOrdering';

        $actionTemplate = new SortRecordActionTemplate;
        $loader = new ActionLoader(new ActionGenerator);
        $actionTemplate->register($loader, 'SortRecordActionTemplate', $arguments);
    }


    public function testGenerationWithRecordClassOption()
    {
        $recordClass = 'OrderingTest\Model\Foo';
        $className = 'OrderingTest\Action\UpdateFooOrdering';

        $loader = new ActionLoader(new ActionGenerator);

        $actionTemplate = new SortRecordActionTemplate;
        $actionTemplate->register($loader, 'SortRecordActionTemplate', array(
            'record_class' => $recordClass,
        ));
        $this->assertCount(1, $loader->getPretreatments());
        $this->assertNotNull($pretreatment = $loader->getPretreatment($className));
        $generatedAction = $actionTemplate->generate($className, $pretreatment);
        $this->assertRequireGeneratedAction($className, $generatedAction);
    }


    public function testSortRecordActionTemplate()
    {
        $actionTemplate = new SortRecordActionTemplate;
        $loader = new ActionLoader(new ActionGenerator);
        $actionTemplate->register($loader, 'SortRecordActionTemplate', array(
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

