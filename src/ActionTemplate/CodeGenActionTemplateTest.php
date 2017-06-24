<?php

namespace WebAction\ActionTemplate;

use WebAction\ActionRunner;
use WebAction\ActionLoader;
use WebAction\ActionGenerator;
use WebAction\GeneratedAction;
use WebAction\Testing\ActionTestCase;

class CodeGenActionTemplateTest extends ActionTestCase
{


    /**
     * @dataProvider classNameProvider
     */
    public function testCodeGenTemplateActionSuccessfulGenerationWithExtra($className)
    {
        $loader = new ActionLoader(new ActionGenerator);
        $actionTemplate = new CodeGenActionTemplate();
        $actionTemplate->register($loader, 'CodeGenActionTemplate', array(
            'action_class' => $className,
            'use' => ['TestApp\Database'],
            'extends' => 'Action',
            'constants' => [
                'foo' => 123
            ],
        ));
        $this->assertCount(1, $loader->getPretreatments());
        $this->assertNotNull($pretreatment = $loader->getPretreatment($className));

        $generatedAction = $actionTemplate->generate($className, $pretreatment['arguments']);
        $this->assertRequireGeneratedAction($className, $generatedAction);
    }


    /**
     * @dataProvider classNameProvider
     */
    public function testCodeGenTemplateActionSuccessfulGeneration($className)
    {
        $loader = new ActionLoader(new ActionGenerator);

        $actionTemplate = new CodeGenActionTemplate();
        $actionTemplate->register($loader, 'CodeGenActionTemplate', array(
            'action_class' => $className,
            'extends' => 'Action',
        ));
        $this->assertCount(1, $loader->getPretreatments());
        $this->assertNotNull($pretreatment = $loader->getPretreatment($className));

        $generatedAction = $actionTemplate->generate($className, $pretreatment['arguments']);
        $this->assertRequireGeneratedAction($className, $generatedAction);
    }

}

