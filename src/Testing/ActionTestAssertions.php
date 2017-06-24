<?php
namespace WebAction\Testing;

use WebAction\ActionTemplate\CodeGenActionTemplate;
use WebAction\ActionTemplate\RecordActionTemplate;
use WebAction\ActionRunner;
use WebAction\Action;
use WebAction\ActionRequest;
use WebAction\GeneratedAction;
use WebAction\Testing\ActionTestCase;

trait ActionTestAssertions
{
    public function assertRequireGeneratedAction($className, GeneratedAction $generatedAction)
    {
        $this->assertNotNull($generatedAction);
        $generatedAction->load();
        $this->assertTrue(class_exists($className), "$className exists");
    }

    public function assertActionInvokeSuccess(Action $action, array $args, ActionRequest $request = null)
    {
        $ret = $action->handle($args, $request);
        $result = $action->getResult();

        if ($ret !== true) {
            print_r($result); 
        }

        $this->assertTrue($ret, $result->message);


        $this->assertEquals('success', $result->type, $result->message);
        return $result;
    }

    public function assertActionInvokeFail(Action $action, array $args, ActionRequest $request)
    {
        $ret = $action->handle($args, $request);
        $result = $action->getResult();
        $this->assertFalse($ret, $result->message);
        $this->assertEquals('error', $result->type, $result->message);
        return $result;
    }

    public static function assertStringEqualsFile($expectedFile, $actualString, $message = '', $canonicalize = false, $ignoreCase = false)
    {
        if (!file_exists($expectedFile)) {
            file_put_contents($expectedFile, $actualString);
            echo PHP_EOL, "Added expected file: ", $expectedFile, PHP_EOL;
            echo "=========================================", PHP_EOL;
            echo $actualString, PHP_EOL;
            echo "=========================================", PHP_EOL;
        }
        return parent::assertStringEqualsFile($expectedFile, $actualString, $message, $canonicalize, $ignoreCase);
    }

    public static function assertFileEquals($expectedFile, $actualFile, $message = '', $canonicalize = false, $ignoreCase = false)
    {
        if (!file_exists($expectedFile)) {
            copy($actualFile, $expectedFile);
            echo PHP_EOL, "Added expected file: ", $expectedFile, PHP_EOL;
            echo "=========================================", PHP_EOL;
            echo file_get_contents($expectedFile), PHP_EOL;
            echo "=========================================", PHP_EOL;
        }
        return parent::assertFileEquals($expectedFile, $actualFile, $message, $canonicalize, $ignoreCase);
    }
}
