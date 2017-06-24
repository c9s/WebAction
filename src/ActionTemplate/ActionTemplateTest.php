<?php

namespace WebAction\ActionTemplate;

use WebAction\Testing\ActionTestCase;
use WebAction\ActionRunner;
use WebAction\ActionLoader;
use WebAction\ActionGenerator;

class ActionTemplateTest extends ActionTestCase
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
     * @expectedException WebAction\Exception\RequiredConfigKeyException
     */
    public function testRecordActionTemplateFailingArguments($arguments)
    {
        $loader = new ActionLoader(new ActionGenerator);

        $actionTemplate = new RecordActionTemplate();
        $actionTemplate->register($loader, 'RecordActionTemplate', $arguments);
    }

    public function testRecordActionTemplate()
    {
        $loader = new ActionLoader(new ActionGenerator);

        $actionTemplate = new RecordActionTemplate();
        $actionTemplate->register($loader, 'RecordActionTemplate', array(
            'namespace' => 'test2',
            'model' => 'test2Model',   // model's name
            'types' => array(
                [ 'prefix' => 'Create'],
                [ 'prefix' => 'Update'],
                [ 'prefix' => 'Delete'],
                [ 'prefix' => 'BulkDelete']
            )
        ));

        $className = 'test2\Action\Updatetest2Model';
        $this->assertCount(4, $loader->getPretreatments());
        $this->assertNotNull($pretreatment = $loader->getPretreatment($className));

        $generatedAction = $actionTemplate->generate($className, $pretreatment);
        $this->assertRequireGeneratedAction($className, $generatedAction);
    }

}
