<?php

namespace WebAction;

use WebAction\ActionTemplate\TwigActionTemplate;
use WebAction\ActionTemplate\RecordActionTemplate;
use WebAction\ActionTemplate\CodeGenActionTemplate;
use WebAction\Testing\ActionTestAssertions;
use User\Model\UserSchema;

class CreateUserWithMoniker extends Action
{
    const moniker = 'create-user';

    public function run()
    {
        return $this->success('test', ["name" => "foo"]);
    }
}

/**
 * @group maghead
 */
class ActionRunnerTest extends \Maghead\Testing\ModelTestCase
{
    use ActionTestAssertions;

    public function models()
    {
        return [new UserSchema];
    }

    public function testMonikerAction()
    {
        $container = new DefaultConfigurations;
        $runner = new ActionRunner($container['loader']);
        $runner->run('WebAction::CreateUserWithMoniker', []);
        $result = $runner->getResult('create-user');
        $this->assertNotNull($result);
        $this->assertEquals("foo", $result->data('name'));
    }

    public function testRegisterAction()
    {
        $container = new DefaultConfigurations;

        $generator = $container['generator'];
        $generator->registerTemplate('TwigActionTemplate', new TwigActionTemplate);

        $loader = new ActionLoader($generator);
        $loader->registerTemplateAction('TwigActionTemplate', [
            'template' => '@WebAction/RecordAction.html.twig',
            'action_class' => 'User\\Action\\BulkCreateUser',
            'variables' => [
                'record_class' => 'User\\Model\\User',
                'base_class' => 'WebAction\\RecordAction\\CreateRecordAction',
            ]
        ]);

        $runner = new ActionRunner($loader);
        $result = $runner->run('User::Action::BulkCreateUser', [
            'email' => 'foo@foo'
        ]);
        $this->assertNotNull($result);
    }

    public function testRegisterActionWithTwig()
    {
        $container = new DefaultConfigurations;
        $generator = $container['generator'];
        $generator->registerTemplate('TwigActionTemplate', new TwigActionTemplate($container['twig_loader']));


        $loader = new ActionLoader($generator);
        $loader->autoload();
        $loader->registerTemplateAction('TwigActionTemplate', array(
            'template' => '@WebAction/RecordAction.html.twig',
            'action_class' => 'User\\Action\\BulkCreateUser',
            'variables' => array(
                'record_class' => 'User\\Model\\User',
                'base_class' => 'WebAction\\RecordAction\\CreateRecordAction'
            )
        ));


        $runner = new ActionRunner($loader);

        $result = $runner->run('User::Action::BulkCreateUser',array(
            'email' => 'foo@foo'
        ));
        $this->assertNotNull($result);
    }

    public function testRunAndJsonOutput()
    {
        $container = new DefaultConfigurations;
        $generator = $container['generator'];
        $generator->registerTemplate('RecordActionTemplate', new RecordActionTemplate);

        $loader = new ActionLoader($generator);
        $loader->registerTemplateAction('RecordActionTemplate', array(
            'namespace' => 'User',
            'model' => 'User',
            'types' => [
                [ 'prefix' => 'Create'],
                [ 'prefix' => 'Update'],
                [ 'prefix' => 'Delete'],
            ]
        ));
        $loader->autoload();

        $runner = new ActionRunner($loader);

        $result = $runner->run('User::Action::CreateUser',[ 
            'email' => 'foo@foo'
        ]);
        $this->assertInstanceOf('WebAction\\Result', $result);

        $json = $result->__toString();
        $this->assertNotNull($json, 'json output');
        $data = json_decode($json);
        $this->assertTrue($data->success);
        $this->assertNotNull($data->data);
        $this->assertNotNull($data->data->id);

        $results = $runner->getResults();
        $this->assertNotEmpty($results);

        $this->assertNotNull($runner->getResult('User::Action::CreateUser'));


        $runner->setResult('test', 'test message');
        $this->assertEquals(true, $runner->hasResult('test'));
        $runner->removeResult('test');
        $this->assertEquals(false, $runner->hasResult('test'));
    }

    public function testHandleWith()
    {
        $container = new DefaultConfigurations;
        $runner = new ActionRunner($container['loader']);

        $stream = fopen('php://memory', 'rw');
        $result = $runner->handleWith($stream, array(
            'action' => 'User::Action::CreateUser',
            '__ajax_request' => 1,
            'email' => 'foo@foo'
        ));
        $this->assertEquals(true, $result);
        fseek($stream, 0);
        $output = stream_get_contents($stream);
        $this->assertStringEqualsFile('tests/fixture/handle_with_result.json', $output);
    }


    public function testRunnerArrayAccess()
    {
        $container = new DefaultConfigurations;
        $runner = new ActionRunner($container['loader']);

        $runner['User::Action::CreateUser'] = new \WebAction\Result;

        $this->assertTrue( isset($runner['User::Action::CreateUser']) );

        // Test Result getter
        $this->assertNotNull($runner['User::Action::CreateUser']);
    }


    /**
    *   @expectedException  WebAction\Exception\InvalidActionNameException
    */
    public function testHandleWithInvalidActionNameException()
    {
        $container = new DefaultConfigurations;
        $runner = new ActionRunner($container['loader']);
        $result = $runner->handleWith(STDOUT, array(
            'action' => "_invalid"
        ));
    }

    /**
    *   @expectedException  WebAction\Exception\InvalidActionNameException
    */
    public function testHandleWithInvalidActionNameExceptionWithEmptyActionName()
    {
        $container = new DefaultConfigurations;
        $runner = new ActionRunner($container['loader']);
        $result = $runner->handleWith(STDOUT, array());  
        
    }

    /**
    *   @expectedException  WebAction\Exception\ActionNotFoundException
    */
    public function testHandleWithActionNotFoundException()
    {
        $container = new DefaultConfigurations;
        $runner = new ActionRunner($container['loader']);
        $result = $runner->handleWith(STDOUT, array(
            'action' => "User::Action::NotFoundAction",
        )); 
    }

    /**
    *   @expectedException  WebAction\Exception\InvalidActionNameException
    */
    public function testRunnerWithInvalidActionNameException()
    {
        $container = new DefaultConfigurations;
        $runner = new ActionRunner($container['loader']);
        $result = $runner->run('!afers');
    }

    /**
    *   @expectedException  WebAction\Exception\ActionNotFoundException
    */
    public function testRunnerWithActionNotFoundException()
    {
        $container = new DefaultConfigurations;
        $runner = new ActionRunner($container['loader']);
        $result = $runner->run('Product::Action::Product');
    }

}
