<?php
namespace WebAction;

use WebAction\Action;

interface ActionLogger
{
    public function log(Action $action);
}
