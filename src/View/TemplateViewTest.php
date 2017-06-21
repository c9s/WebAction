<?php

namespace WebAction\View;

class TemplateViewTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $actionClass = \WebAction\RecordAction\BaseRecordAction::createCRUDClass(\User\Model\User::class,'Create');

        $action = new $actionClass;
        $this->assertNotNull($action);

        $view = new \FooTemplateView($action);
        $this->assertNotNull($view);
        $this->assertNotNull($view->render());
    }
}

