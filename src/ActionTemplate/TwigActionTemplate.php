<?php

namespace WebAction\ActionTemplate;

use WebAction\ActionRunner;
use WebAction\ActionLoader;
use WebAction\GeneratedAction;
use WebAction\Exception\RequiredConfigKeyException;
use Twig_Loader_Filesystem;
use Twig_Environment;
use ReflectionClass;

/**
 *  File-Based Action Template Synopsis
 *    To generate from template file
 *
 *    $actionTemplate = new TwigActionTemplate();
 *
 *    $loader = new WebAction\ActionLoader;
 *    $actionTemplate->register($loader, 'TwigActionTemplate', array(
 *        'action_class' => 'User\\Action\\BulkUpdateUser',
 *        'template' => '@WebAction/RecordAction.html.twig',
 *        'variables' => array(
 *            'record_class' => 'User\\Model\\User',
 *            'base_class' => 'WebAction\\RecordAction\\CreateRecordAction'
 *        )
 *    ));
 *
 *    $className = 'User\Action\BulkUpdateUser';
 *
 *    $generatedAction = $actionTemplate->generate($className,
 *        $loader->pretreatments[$className]['actionArgs']);
 *
 *    $generatedAction->requireAt($cacheCodePath);
 *
 *
 * Depends on Twig template engine
 */

class TwigActionTemplate implements ActionTemplate
{
    private $templateDirs = array();

    protected $loader;

    protected $env;

    public function __construct(Twig_Loader_Filesystem $loader = null, Twig_Environment $env = null)
    {
        if (!$loader) {
            $refClass = new ReflectionClass('WebAction\\ActionGenerator');
            $templateDirectory = dirname($refClass->getFilename()) . DIRECTORY_SEPARATOR . 'Templates';

            // add WebAction built-in template path
            $loader = new Twig_Loader_Filesystem([]);
            $loader->addPath($templateDirectory, 'WebAction');
        }
        $this->loader = $loader;
        if (!$env) {
            $env = new Twig_Environment($this->loader, array(
                'cache' => false,
            ));
        }
        $this->env = $env;
    }

    /**
     *  @synopsis
     *
     *      $template->register($loader,
     *          'templateName',
     *          array(
     *              'action_class' => 'User\\Action\\BulkUpdateUser',
     *              'template' => '@WebAction/RecordAction.html.twig',
     *              'variables' => array(
     *                  'record_class' => 'User\\Model\\User',
     *                  'base_class' => 'WebAction\\RecordAction\\CreateRecordAction'
     *              )
     *      ));
     */
    public function register(ActionLoader $loader, $asTemplate, array $options = array())
    {
        // $targetActionClass, $template, $variables
        if (!isset($options['action_class'])) {
            throw new RequiredConfigKeyException('action_class');
        }
        $class = $options['action_class'];

        if (!isset($options['template'])) {
            throw new RequiredConfigKeyException('template');
        }
        $template = $options['template'];

        if (!isset($options['variables'])) {
            throw new RequiredConfigKeyException('variables');
        }
        $variables = $options['variables'];

        $loader->register($class, $asTemplate, [
            'template' => $template,
            'variables' => $variables
        ]);
    }
    
    /**
     * @synopsis
     *     $generatedAction = $template->generate('User\Action\BulkUpdateUser',  // class name
     *          [
     *              'template' => '@WebAction/RecordAction.html.twig',
     *              'variables' => array(
     *                  'record_class' => 'User\\Model\\User',
     *                  'base_class' => 'WebAction\\RecordAction\\CreateRecordAction'
     *              )
     *          ]);
     */
    public function generate($action_class, array $options = array())
    {
        if (!isset($options['template'])) {
            throw new RequiredConfigKeyException('template is not defined.');
        }
        $template = $options['template'];

        if (!isset($options['variables'])) {
            throw new RequiredConfigKeyException('variables is not defined.');
        }
        $variables = $options['variables'];

        $parts = explode("\\", $action_class);
        $variables['target'] = array();
        $variables['target']['classname'] = array_pop($parts);
        $variables['target']['namespace'] = join("\\", $parts);
        $code = $this->env->render($template, $variables);
        return new GeneratedAction($action_class, $code);
    }

    public function getTwigEnvironment()
    {
        return $this->env;
    }

    public function getTwigLoader()
    {
        return $this->loader;
    }
}
