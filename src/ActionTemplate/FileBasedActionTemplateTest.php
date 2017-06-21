<?php

namespace WebAction\ActionTemplate;

use WebAction\ActionRunner;
use WebAction\ActionGenerator;
use WebAction\RecordAction\BaseRecordAction;
use WebAction\Testing\ActionTestCase;
use Twig_Loader_Filesystem;
use Twig_Environment;

class TwigActionTemplateTest extends ActionTestCase
{
    public function failingArgumentProvider()
    {
        return [ 
            [ [] ],
            [ ['action_class' => 'FileApp\\Action\FooAction'] ],
            [ [
                'action_class' => 'FileApp\\Action\FooAction',
                'template' => '@WebAction\RecordAction.html.twig',
            ] ],
        ];
    }

    /**
     * @dataProvider failingArgumentProvider
     * @expectedException WebAction\Exception\RequiredConfigKeyException
     */
    public function testTwigActionTemplateWithException($arguments)
    {
        $actionTemplate = new TwigActionTemplate;
        $generator = new ActionGenerator();
        $generator->registerTemplate('TwigActionTemplate', $actionTemplate);

        $runner = new ActionRunner;
        $actionTemplate->register($runner, 'TwigActionTemplate', $arguments);

        $generator->generate('TwigActionTemplate', 'FileApp\Action\FooAction', $arguments);
    }


    public function testTwigActionTemplateWithTwigEnvironmentAndLoader()
    {
        $loader = new Twig_Loader_Filesystem([]);
        $loader->addPath('src/Templates', 'WebAction');

        $env = new Twig_Environment($loader, array(
            'cache' => false,
        ));

        $actionTemplate = new TwigActionTemplate($loader, $env);

        $this->assertNotNull($actionTemplate->getTwigEnvironment());
        $this->assertNotNull($actionTemplate->getTwigLoader());

        $runner = new ActionRunner;
        $className = 'User\\Action\\BulkUpdateUser4';
        $actionTemplate->register($runner, 'TwigActionTemplate', array(
            'action_class' => $className,
            'template' => '@WebAction/RecordAction.html.twig',
            'variables' => [
                'record_class' => \User\Model\User::class,
                'base_class' => \WebAction\RecordAction\CreateRecordAction::class,
            ]
        ));
        $this->assertCount(1, $runner->getPretreatments());
        $this->assertNotNull($pretreatment = $runner->getActionPretreatment($className));

        $generatedAction = $actionTemplate->generate($className, $pretreatment['arguments']);

        $this->assertRequireGeneratedAction($className, $generatedAction);

        $this->assertFileEquals('tests/fixture/bulk_update_user4.php', $generatedAction->getRequiredPath());
    }



    public function testTwigActionTemplateWithTwigLoader()
    {
        $loader = new Twig_Loader_Filesystem([]);
        $loader->addPath('src/Templates', 'WebAction');

        $actionTemplate = new TwigActionTemplate($loader);

        $runner = new ActionRunner;
        $className = 'User\\Action\\BulkUpdateUser3';
        $actionTemplate->register($runner, 'TwigActionTemplate', array(
            'action_class' => $className,
            'template' => '@WebAction/RecordAction.html.twig',
            'variables' => [
                'record_class' => \User\Model\User::class,
                'base_class' => \WebAction\RecordAction\CreateRecordAction::class,
            ]
        ));
        $this->assertCount(1, $runner->getPretreatments());
        $this->assertNotNull($pretreatment = $runner->getActionPretreatment($className));

        $generatedAction = $actionTemplate->generate($className, $pretreatment['arguments']);
        $this->assertRequireGeneratedAction($className, $generatedAction);
    }

    public function testTwigActionTemplate()
    {
        $actionTemplate = new TwigActionTemplate();
        $runner = new ActionRunner;
        $className = 'User\\Action\\BulkUpdateUser2';
        $actionTemplate->register($runner, 'TwigActionTemplate', array(
            'action_class' => $className,
            'template' => '@WebAction/RecordAction.html.twig',
            'variables' => array(
                'record_class' => \User\Model\User::class,
                'base_class' => \WebAction\RecordAction\CreateRecordAction::class,
            )
        ));
        $this->assertCount(1, $runner->getPretreatments());
        $this->assertNotNull($pretreatment = $runner->getActionPretreatment($className));

        $generatedAction = $actionTemplate->generate($className, $pretreatment['arguments']);
        $this->assertNotNull($generatedAction);
        $generatedAction->load();
        $this->assertNotNull(class_exists($className));
    }
}



