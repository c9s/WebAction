<?php
use ActionKit\ActionTemplate\SampleActionTemplate;
use ActionKit\ActionTemplate\RecordActionTemplate;
use ActionKit\ActionTemplate\FileBasedActionTemplate;
use ActionKit\ActionTemplate\UpdateOrderingRecordActionTemplate;
use ActionKit\Testing\ActionTestCase;
use ActionKit\ActionRunner;

class ActionTemplate extends ActionTestCase
{

    public function failingArgumentProvider()
    {
        return [ 
            [ [] ],
            [ [
                'namespace' => 'test2',
            ] ],
            [ [
                'namespace' => 'test2',
                'model' => 'test2Model',   // model's name
            ] ],
        ];
    }

    /**
     * @dataProvider failingArgumentProvider
     * @expectedException ActionKit\Exception\RequiredConfigKeyException
     */
    public function testRecordActionTemplateFailingArguments($arguments)
    {
        $actionTemplate = new RecordActionTemplate();
        $runner = new ActionRunner;
        $actionTemplate->register($runner, 'RecordActionTemplate', $arguments);
    }

    public function testRecordActionTemplate()
    {
        $actionTemplate = new RecordActionTemplate();
        $runner = new ActionRunner;
        $actionTemplate->register($runner, 'RecordActionTemplate', array(
            'namespace' => 'test2',
            'model' => 'test2Model',   // model's name
            'types' => array(
                [ 'name' => 'Create'],
                [ 'name' => 'Update'],
                [ 'name' => 'Delete'],
                [ 'name' => 'BulkDelete']
            )
        ));

        $className = 'test2\Action\Updatetest2Model';
        $this->assertCount(4, $runner->getPretreatments());
        $this->assertNotNull($pretreatment = $runner->getActionPretreatment($className));

        $generatedAction = $actionTemplate->generate($className, $pretreatment);
        $this->assertRequireGeneratedAction($className, $generatedAction);
    }

}
