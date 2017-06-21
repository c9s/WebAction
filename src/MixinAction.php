<?php
namespace WebAction;

use RuntimeException;

/**
 * @codeCoverageIgnore
 */
class MixinAction
{
    protected $_action;

    public function __construct($action)
    {
        $this->_action = $action;
    }

    public function preinit()
    {
    }

    public function postinit()
    {
    }

    public function beforeRun()
    {
        return true;
    }

    public function afterRun()
    {
        return true;
    }

    public function run()
    {
        return true;
    }

    public function schema()
    {
        /*
        $this->param('...');
        */
    }

    public function __get($k)
    {
        return $this->_action->$k;
    }

    public function __set($k, $v)
    {
        return $this->_action->$k = $v;
    }

    public function __call($m, $args)
    {
        if (method_exists($this->_action, $m)) {
            return call_user_func_array(array($this->_action,$m), $args);
        } else {
            throw new RuntimeException("Method $m is not defined in " . get_class($this));
        }
    }
}
