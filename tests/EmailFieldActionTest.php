<?php

use WebAction\Action;
use WebAction\ActionRequest;

class EmailFieldTestAction extends Action
{
    public function schema()
    {
        $this->param('email')
            ->isa('Email');
    }
}



class EmailFieldActionTest extends \PHPUnit\Framework\TestCase
{
    public function testInvalidEmailFieldAction()
    {
        $args = [ 'email' => 'yoanlin93' ];
        $action = new EmailFieldTestAction($args);
        $ret = $action->handle(new ActionRequest($args));
        $this->assertFalse($ret);
    }

    public function testEmailFieldAction()
    {
        $args = [ 'email' => 'yoanlin93@gmail.com' ];
        $action = new EmailFieldTestAction($args);
        $ret = $action->handle(new ActionRequest($args));
        $this->assertTrue($ret);
    }

}
