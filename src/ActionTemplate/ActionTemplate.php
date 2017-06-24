<?php
namespace WebAction\ActionTemplate;

use WebAction\ActionLoader;

interface ActionTemplate
{
    public function register(ActionLoader $loader, $asTemplate, array $options = array());

    public function generate($actionClass, array $options = array());
}
