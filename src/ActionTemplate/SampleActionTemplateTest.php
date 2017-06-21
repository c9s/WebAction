<?php
use WebAction\ActionRunner;
use WebAction\ActionGenerator;
use WebAction\RecordAction\BaseRecordAction;
use WebAction\ActionTemplate\RecordActionTemplate;
use WebAction\ActionTemplate\TwigActionTemplate;
use WebAction\ActionTemplate\SampleActionTemplate;
use WebAction\GeneratedAction;

class SampleActionTemplateTest extends \PHPUnit\Framework\TestCase
{

    public function failingArgumentProvider()
    {
        return [ 
            [ ['namespace' => 'FooBar'] ],
            [ ['action_name' => 'CreateSample'] ],
            [ [] ]
        ];
    }

    /**
     * @dataProvider failingArgumentProvider
     * @expectedException WebAction\Exception\RequiredConfigKeyException
     */
    public function testSampleActionTemplateWithException($arguments)
    {
        $generator = new ActionGenerator();
        $generator->registerTemplate('SampleActionTemplate', new SampleActionTemplate());
        $generator->generate('SampleActionTemplate', 'SampleAction', $arguments);
    }

    public function testSampleActionTemplate()
    {
        $generator = new ActionGenerator();
        $generator->registerTemplate('SampleActionTemplate', new SampleActionTemplate());
        $runner = new ActionRunner([ 'generator' => $generator ]);
        // $runner->registerAction('SampleActionTemplate', array('action_class' => 'SampleAction'));
        $action = $runner->getGenerator()->generate('SampleActionTemplate', 'SampleAction', [ 
            'namespace' => 'FooBar',
            'action_name' => 'CreateSample'
        ]);
        $this->assertInstanceOf(GeneratedAction::class, $action);
    }
}

