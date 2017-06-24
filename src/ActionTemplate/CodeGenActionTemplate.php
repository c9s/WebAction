<?php
namespace WebAction\ActionTemplate;

use WebAction\ActionLoader;
use WebAction\GeneratedAction;
use WebAction\Exception\RequiredConfigKeyException;
use Exception;
use CodeGen\ClassFile;

/**
 *  CodeGen-Based Action Template Synopsis
 *
 *      $actionTemplate = new CodeGenActionTemplate();
 *      $runner = new WebAction\ActionRunner;
 *      $actionTemplate->register($runner, 'CodeGenActionTemplate', array(
 *          'namespace' => 'test2',
 *          'model' => 'test2Model',   // model's name
 *          'types' => array('Create','Update','Delete','BulkDelete')
 *      ));
 *
 *      $className = 'test2\Action\UpdatetestModel';
 *      $generatedAction = $actionTemplate->generate($className, [
 *          'extends' => "\\WebAction\\RecordAction\\CreateRecordAction",
 *          'properties' => [
 *              'recordClass' => "test2\\Model\\testModel",
 *          ],
 *      ]);
 *
 *      $generatedAction->requireAt($cacheCodePath);
 *
 */
class CodeGenActionTemplate implements ActionTemplate
{
    /**
     * @synopsis
     *
     *    $template->register($runner, [
     *       'action_class' => 'FooAction',
     *       'extends' => "\\WebAction\\RecordAction\\{$type}RecordAction",
     *       'properties' => [
     *           'recordClass' => $options['namespace'] . "\\Model\\" . $options['model'],
     *       ],
     *    ]);
     */
    public function register(ActionLoader $loader, $asTemplate, array $options = array())
    {
        if (isset($options['use'])) {
            array_unshift($options['use'], '\\WebAction\\Action');
        } else {
            $options['use'] = ['\\WebAction\\Action'];
        }

        $loader->register($options['action_class'], $asTemplate, $options);
    }

    public function createActionClassFile($actionClass, array $options = array())
    {
        $class = new ClassFile($actionClass);
        if (isset($options['use'])) {
            foreach ($options['use'] as $use) {
                $class->useClass($use);
            }
        }
        if (isset($options['extends'])) {
            $class->extendClass($options['extends']);
        }
        if (isset($options['properties'])) {
            foreach ($options['properties'] as $name => $value) {
                $class->addProperty($name, $value);
            }
        }
        if (isset($options['constants'])) {
            foreach ($options['constants'] as $name => $value) {
                $class->addConst($name, $value);
            }
        }
        if (isset($options['traits'])) {
            foreach ($options['traits'] as $traitClass) {
                $class->useTrait($traitClass);
            }
        }

        return $class;
    }

    public function generate($actionClass, array $options = array())
    {
        $class = $this->createActionClassFile($actionClass, $options);
        $code = $class->render();
        return new GeneratedAction($actionClass, $code, $class);
    }
}
