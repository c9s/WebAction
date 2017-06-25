<?php

namespace WebAction\Action;

use WebAction\Action;
use WebAction\Utils;
use WebAction\Exception\InvalidActionNameException;

class QueryAction extends Action
{
    public function schema()
    {
        $this->param('action')
            ->isa('Str')
            ->required();

        $this->param('field')
            ->isa('Str')
            ->required();

        $this->param('attribute')
            ->isa('Str')
            ->required();
    }

    public function run()
    {
        $actionClass = $this->arg("action");

        if (!Utils::validateActionName($actionClass)) {
            throw new InvalidActionNameException("Invalid action name: $actionClass.");
        }

        $actionClass = Utils::toActionClass($actionClass);

        if (!class_exists($actionClass, true)) {
            return $this->error("$actionClass not found.");
        }

        $action = new $actionClass;

        $field = $this->arg("field");

        $param = $action->getParam($field);

        $attr = $this->arg("attribute");

        switch ($attr) {
        case "validValues":
            return $this->success("Field found", [ "validValues" => $param->getValidValues() ]);
            break;
        }

        return $this->error("$attr is unsupported.");
    }
}
