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
        $action = new EmailFieldTestAction([ 'email' => 'yoanlin93' ]);
        $ret = $action->handle([], new ActionRequest());
        $this->assertFalse($ret);
    }

    public function testEmailFieldAction()
    {
        $action = new EmailFieldTestAction([ 'email' => 'yoanlin93@gmail.com' ]);
        $ret = $action->handle([], new ActionRequest());
        $this->assertTrue($ret);
    }

}
