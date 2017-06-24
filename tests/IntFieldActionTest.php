<?php

use WebAction\Action;
use WebAction\ActionRequest;


class IntFieldTestAction extends Action
{

    public function schema()
    {
        $this->param('cnt')
            ->isa('Int');
    }

}


class IntFieldActionTest extends \PHPUnit\Framework\TestCase
{

    public function testIntFieldAction()
    {
        $args = ['cnt' => 10];
        $action = new IntFieldTestAction($args);
        $ret = $action->handle(new ActionRequest($args));
        $this->assertTrue($ret);
    }

}
