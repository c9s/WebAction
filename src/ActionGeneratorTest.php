<?php

namespace WebAction;

use WebAction\RecordAction\BaseRecordAction;
use WebAction\ActionTemplate\RecordActionTemplate;
use WebAction\ActionTemplate\TwigActionTemplate;
use WebAction\ActionTemplate\SampleActionTemplate;
use WebAction\ActionTemplate\ActionTemplate;

class ActionGeneratorTest extends \PHPUnit\Framework\TestCase
{
    // TODO: should be moved to BaseRecordActionTest
    public function testCRUDClassFromBaseRecordAction()
    {
        $class = BaseRecordAction::createCRUDClass('App\Model\Post', 'Create');
        $this->assertNotNull($class);
        $this->assertEquals('App\Action\CreatePost', $class);
    }


    /**
     * @expectedException WebAction\Exception\RequiredConfigKeyException
     */
    public function testRequiredConfigKeyException()
    {
        $generator = new ActionGenerator();
        $generator->registerTemplate('RecordActionTemplate', new RecordActionTemplate());

        $loader = new ActionLoader($generator);
        $loader->registerTemplateAction('RecordActionTemplate', array());

        $runner = new ActionRunner($loader);
    }

    /**
     * @expectedException WebAction\Exception\UndefinedTemplateException
     */
    public function testUndefinedTemplate()
    {
        $generator = new ActionGenerator();
        $template = $generator->getTemplate('UndefinedTemplate');
    }

    public function testTemplateGetter()
    {
        $generator = new ActionGenerator();
        $generator->registerTemplate('RecordActionTemplate', new RecordActionTemplate());
        $template = $generator->getTemplate('RecordActionTemplate');
        $this->assertInstanceOf(ActionTemplate::class, $template);
    }

    public function testGeneratedUnderDirectory()
    {
        $generator = new ActionGenerator();
        $generator->registerTemplate('RecordActionTemplate', new RecordActionTemplate());

        $loader = new ActionLoader($generator);
        $runner = new ActionRunner($loader);

        $actionArgs = array(
            'namespace' => 'test',
            'model' => 'testModel',
            'types' => array(
                [ 'prefix' => 'Create'],
                [ 'prefix' => 'Update'],
                [ 'prefix' => 'Delete'],
                [ 'prefix' => 'BulkDelete']
            )
        );
        $loader->registerTemplateAction('RecordActionTemplate', $actionArgs);

        $className = 'test\Action\UpdatetestModel';

        @mkdir('tmp', 0755, true);
        $generatedAction = $generator->generateUnderDirectory('tmp', 'RecordActionTemplate', $className, $actionArgs);
        $this->assertNotNull($generatedAction);

        $filePath = 'tmp' . DIRECTORY_SEPARATOR . $generatedAction->getPsrClassPath();
        $this->assertFileExists($filePath, $filePath);
        unlink($filePath);
    }

    public function testGeneratedAt()
    {
        $generator = new ActionGenerator();
        $generator->registerTemplate('RecordActionTemplate', new RecordActionTemplate());

        $loader = new ActionLoader($generator);

        $actionArgs = array(
            'namespace' => 'test',
            'model' => 'testModel',
            'types' => array(
                [ 'prefix' => 'Create'],
                [ 'prefix' => 'Update'],
                [ 'prefix' => 'Delete'],
                [ 'prefix' => 'BulkDelete']
            )
        );
        $loader->registerTemplateAction('RecordActionTemplate', $actionArgs);

        $runner = new ActionRunner($loader);
        $className = 'test\Action\UpdatetestModel';
        $filePath = tempnam('/tmp', md5($className));
        $generatedAction = $generator->generateAt($filePath, 'RecordActionTemplate', $className, $actionArgs);
        $this->assertNotNull($generatedAction);
        $this->assertFileExists($filePath);
        unlink($filePath);
    }

    public function testRecordActionTemplate()
    {
        $generator = new ActionGenerator();
        $generator->registerTemplate('RecordActionTemplate', new RecordActionTemplate());

        $loader = new ActionLoader($generator);

        $actionArgs = array(
            'namespace' => 'test',
            'model' => 'testModel',
            'types' => array(
                [ 'prefix' => 'Create'],
                [ 'prefix' => 'Update'],
                [ 'prefix' => 'Delete'],
                [ 'prefix' => 'BulkDelete']
            )
        );
        $loader->registerTemplateAction('RecordActionTemplate', $actionArgs);

        $runner = new ActionRunner($loader);

        /*
        $template = $generator->getTemplate('RecordActionTemplate');
        $template->register($runner, 'RecordActionTemplate', $actionArgs);
         */

        $this->assertCount(4, $loader->getPretreatments());

        $className = 'test\Action\UpdatetestModel';

        $this->assertNotNull($pretreatment = $loader->getPretreatment($className));

        $generatedAction = $generator->generate('RecordActionTemplate',
            $className,
            $pretreatment['arguments']);

        $generatedAction->load();

        $this->assertNotNull(class_exists($className));
    }

    public function testFildBased()
    {
        $generator = new ActionGenerator();
        $generator->registerTemplate('TwigActionTemplate', new TwigActionTemplate());
        $template = $generator->getTemplate('TwigActionTemplate');
        $this->assertInstanceOf('WebAction\ActionTemplate\ActionTemplate', $template);

        $loader = new ActionLoader($generator);

        $template->register($loader, 'TwigActionTemplate', array(
            'action_class' => 'User\\Action\\BulkUpdateUser',
            'template' => '@WebAction/RecordAction.html.twig',
            'variables' => array(
                'record_class' => 'User\\Model\\User',
                'base_class' => 'WebAction\\RecordAction\\CreateRecordAction'
            )
        ));
        $runner = new ActionRunner($loader);


        $className = 'User\Action\BulkUpdateUser';

        $this->assertCount(1, $loader->getPretreatments());
        $this->assertNotNull($pretreatment = $loader->getPretreatment($className));

        $generatedAction = $generator->generate('TwigActionTemplate',
            $className,
            $pretreatment['arguments']);

        $generatedAction->load();

        $this->assertNotNull(class_exists($className));
    }

    public function testWithoutRegister()
    {
        $generator = new ActionGenerator();
        $generator->registerTemplate('TwigActionTemplate', new TwigActionTemplate());

        $className = 'User\Action\BulkDeleteUser';

        $generatedAction = $generator->generate('TwigActionTemplate',
            $className,
            array(
                'template' => '@WebAction/RecordAction.html.twig',
                'variables' => array(
                    'record_class' => 'User\\Model\\User',
                    'base_class' => 'WebAction\\RecordAction\\CreateRecordAction'
                )
            )
        );
        $generatedAction->load();
        $this->assertNotNull(class_exists($className));
    }
}
