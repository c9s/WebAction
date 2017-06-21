<?php

class FooTemplateView extends WebAction\View\TemplateView
{
    public function render()
    {
        return $this->renderTemplateFile('foo.html',array(  ));
    }
}


