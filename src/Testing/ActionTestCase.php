<?php
namespace WebAction\Testing;

use \PHPUnit\Framework\TestCase;
use WebAction\ActionTemplate\CodeGenActionTemplate;
use WebAction\ActionTemplate\RecordActionTemplate;
use WebAction\ActionRunner;
use WebAction\Action;
use WebAction\GeneratedAction;
use WebAction\Testing\ActionTestCase;

abstract class ActionTestCase extends \PHPUnit\Framework\TestCase
{
    use ActionTestAssertions;

    public static $classCounter = 0;
    public static $classPrefix = 'TestApp\\Action\\Foo';

    public function classNameProvider()
    {
        return [
            [static::$classPrefix . ++static::$classCounter . "Action"]
        ];
    }
}
