<?php
namespace WebAction\ActionTemplate;

use WebAction\ActionRunner;

interface ActionTemplate
{
    public function register(ActionRunner $runner, $asTemplate, array $options = array());
    public function generate($actionClass, array $options = array());
}
