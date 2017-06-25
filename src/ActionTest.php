<?php

namespace WebAction;

use WebAction\Action;
use WebAction\MixinAction;

class FakeMixin extends MixinAction {

    public function schema() {
        $this->param('handle')->type('string');
    }

}


class ImageParamTestAction extends Action {

    public function mixins() {
        return array( 
            new FakeMixin($this),
        );
    }

    public function schema() {
        $this->param('image','Image');
    }
}

class TestTakeFilterAction extends Action {

    public function schema() {
        $this->param('extra1');
        $this->param('extra2');
        $this->param('extra3');
        $this->param('only_this1');
        $this->param('only_this2');
        $this->takes('only_this1','only_this2');
    }

}

class LoginTestAction extends Action 
{
    public function schema()
    {
        $this->param('username');
        $this->param('password');
        $this->filterOut(array('token'));
    }

    public function run()
    {
        // test filterOut
        if ($this->arg('token')) {
            return $this->error('token should be filter out.');
        }
        if ($this->arg('username') === 'admin' &&
            $this->arg('password') === 's3cr3t' ) {
                return $this->success('Login');
        }
        return $this->error('Login Error');
    }
}

class ActionTest extends \PHPUnit\Framework\TestCase
{

    public function testGetParamsWithTakeFilter()
    {
        $take = new TestTakeFilterAction;
        $params = $take->getParams();
        $this->assertNotNull($params,'get params');

        $keys = $take->takeFields;
        foreach( $keys as $k ) {
            $this->assertNotNull( isset($params[$k]), "has key $k");
        }
        $this->assertNotNull( ! isset($params['extra1']) );
        $this->assertNotNull( ! isset($params['extra2']) );
        $this->assertNotNull( ! isset($params['extra3']) );
    }

    public function testCreatingActionWithContainerAsTheOptions()
    {
        $container = new \Pimple\Container;
        $action = new LoginTestAction([
            'username' => 'admin',
            'password' => 's3cr3t',
        ], $container);
        $this->assertTrue($action->handle());
    }

    public function testRender()
    {
        $login = new LoginTestAction;
        $this->assertNotNull($login->render());
        $this->assertNotNull($login->render('username'));
        $this->assertNotNull($login->renderWidget('username'));
        $this->assertNotNull($login->renderField('username'));
        $this->assertNotNull($login->renderLabel('password'));
        $this->assertNotNull($login->renderWidgets(['username', 'password']));
        $this->assertNotNull($login->renderSubmitWidget());
        $this->assertNotNull($login->renderButtonWidget());
        $this->assertNotNull($login->renderSignatureWidget());
    }

    public function testGetParamsWithFilterOut() 
    {
        $login = new LoginTestAction;
        $params = $login->getParams(); // get params with param filter
        $this->assertNotEmpty($params);
        $this->assertCount(2, array_keys($params));
        $this->assertNotNull( !isset($params['token']) , 'Should not include token param.' );
        $this->assertNotNull( isset($params['username']) , 'Should have username param' );
        $this->assertNotNull( isset($params['password']) , 'Should have password param' );
    }

    /*
    public function testImageParam() 
    {
        $action = new ImageParamTestAction(array(
            'image' => 1,
        ));
        $this->assertNotNull($action);
    }
     */

    public function testFilterOut()
    {
        $action = new LoginTestAction([
            'username' => 'admin',
            'password' => 's3cr3t',
            'token' => 'blah',
        ]);

        $success = $action->handle(new ActionRequest([]));
        $result = $action->getResult();
        $this->assertTrue($success, $result->message);

        $result = $action->getResult();
        $this->assertNotNull($result,'Got Result');
        $this->assertEquals('Login', $result->message);
        $this->assertTrue($result->isSuccess());
    }

    public function testParams()
    {
        $login = new LoginTestAction;
        $this->assertEquals('WebAction::LoginTestAction', $login->getName());

        $result = $login->getWidgetsByNames(['username', 'password']);
        $this->assertCount(2, $result);
        $this->assertEquals('Foo', $login->arg('username', 'Foo'));

        $this->assertNotEmpty($login->params());
        $this->assertNotEmpty($login->params(true));

        $this->assertTrue($login->hasParam('username'));
        $this->assertNotNull($removed = $login->removeParam('username'));
        $this->assertFalse($login->hasParam('username'));
    }

    /**
     * @expectedException Exception
     */
    public function testWrongType()
    {
        $login = new LoginTestAction;
        $login->replaceParam('username', 'TestType');
    }
}

