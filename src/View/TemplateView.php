<?php
namespace WebAction\View;

use ReflectionObject;
use Exception;
use Twig_Loader_Filesystem;
use Twig_Environment;
use WebAction\Template;
use WebAction\Action;

abstract class TemplateView
{
    public $action;

    abstract public function render();

    public function __construct(Action $action)
    {
        $this->action = $action;
        $this->template = new Template;
        $this->template->setClassDirFrom($this);
        $this->template->init();
    }

    /**
     * $twig->render('index.html', array('the' => 'variables', 'go' => 'here'));
     * */
    public function renderTemplateFile($templateFile, $arguments = array())
    {
        $arguments = array_merge(array(
            // the view object.
            'View' => $this,

            // the action object.
            'Action' => $this->action
        ), $arguments);
        return $this->template->render($templateFile, $arguments);
    }

    public function __toString()
    {
        return $this->render();
    }
}
