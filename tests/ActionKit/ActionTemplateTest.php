<?php
use ActionKit\ActionTemplate\SampleActionTemplate;
use ActionKit\ActionTemplate\RecordActionTemplate;
use ActionKit\ActionTemplate\FileBasedActionTemplate;
use ActionKit\ActionTemplate\UpdateOrderingRecordActionTemplate;

class ActionTemplate extends PHPUnit_Framework_TestCase
{

    public function testSampleActionTemplate()
    {
        $actionTemplate = new SampleActionTemplate();
        $generatedAction = $actionTemplate->generate('', array(
            'namespace' => 'Core',
            'action_name' => 'GrantAccess'
        ));
        ok( $generatedAction );

        $generatedAction->load();

        is( 'Core\\Action\\GrantAccess' , $generatedAction->className );
        ok( class_exists( 'Core\\Action\\GrantAccess' ) );
    }

    public function testUpdateOrderingRecordActionTemplate()
    {
        $actionTemplate = new UpdateOrderingRecordActionTemplate;
        $runner = new ActionKit\ActionRunner;
        $actionTemplate->register($runner, 'UpdateOrderingRecordActionTemplate', array(
            'namespace' => 'test2',
            'model' => 'Test2Model'   // model's name
        ));

        $className = 'test2\Action\UpdateTest2ModelOrdering';

        $this->assertCount(1, $runner->getPretreatments());
        $this->assertNotNull($pretreatment = $runner->getActionPretreatment($className));

        $generatedAction = $actionTemplate->generate($className, $pretreatment);

        $this->assertNotNull($generatedAction);

        $generatedAction->load();
        ok( class_exists( $className ) );
    }

    public function testRecordActionTemplate()
    {
        $actionTemplate = new RecordActionTemplate();
        $runner = new ActionKit\ActionRunner;
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
        $this->assertNotNull($generatedAction);
        $generatedAction->load();
        ok( class_exists( $className ) );
    }

    public function testFildBasedTemplate()
    {
        $actionTemplate = new FileBasedActionTemplate();

        $runner = new ActionKit\ActionRunner;

        $className = 'User\\Action\\BulkUpdateUser2';

        $actionTemplate->register($runner, 'FileBasedActionTemplate', array(
            'action_class' => $className,
            'template' => '@ActionKit/RecordAction.html.twig',
            'variables' => array(
                'record_class' => 'User\\Model\\User',
                'base_class' => 'ActionKit\\RecordAction\\CreateRecordAction'
            )
        ));
        $this->assertCount(1, $runner->getPretreatments());
        $this->assertNotNull($pretreatment = $runner->getActionPretreatment($className));

        $generatedAction = $actionTemplate->generate($className, $pretreatment['arguments']);
        $this->assertNotNull($generatedAction);
        $generatedAction->load();
        ok( class_exists( $className ) );
    }
}
