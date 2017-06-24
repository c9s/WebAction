<?php

namespace WebAction;

class ActionLoader
{
    protected $pretreatments = [];

    protected $generator;

    protected $cacheDir;

    public function __construct(ActionGenerator $generator, $cacheDir = null)
    {
        $this->generator = $generator;
        $this->cacheDir = $cacheDir ?: __DIR__ . DIRECTORY_SEPARATOR . 'Cache';

        if ($this->cacheDir && ! file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }


    /**
     * Generate an action class with pretreatment config.
     *
     * This method could be used when you want to customize more about the
     * generated action class rather than the default pretreatment.
     *
     * Note the pretreatment argument can be ignored if you want to use the
     * default pretreatment config.
     *
     * @param string $class action class name
     * @param array $pretreatment the pretreatment config array
     * @return GeneratedAction
     */
    protected function generate($class, array $pretreatment)
    {
        if (!$pretreatment) {
            if (!isset($this->pretreatments[$class])) {
                return false;
            }
            $pretreatment = $this->pretreatments[$class];
        }
        return $this->generator->generate($pretreatment['template'], $class, $pretreatment['arguments']);
    }


    /**
     * load trigger the action class generation if the class doesn't
     * exist and loads the action class.
     *
     * @param string $class action class
     */
    public function load($class)
    {
        if (!isset($this->pretreatments[$class])) {
            return false;
        }

        $pretreatment = $this->pretreatments[$class];
        if ($this->loadClassCache($class, $pretreatment['arguments'])) {
            return true;
        }

        $generatedAction = $this->generate($class, $pretreatment);
        $cacheFile = $this->getClassCacheFile($class, $pretreatment['arguments']);
        $generatedAction->requireAt($cacheFile);
        return true;
    }

    /**
     * Return the cache path of the class name
     *
     * @param string $className
     * @return string path
     */
    protected function getClassCacheFile($className, array $params = array())
    {
        $chk = !empty($params) ? md5(serialize($params)) : '';
        return $this->cacheDir . DIRECTORY_SEPARATOR . str_replace('\\', '_', $className) . $chk . '.php';
    }

    /**
     * Load the class cache file
     *
     * @param string $className the action class
     */
    protected function loadClassCache($className, array $params = array())
    {
        $file = $this->getClassCacheFile($className, $params);
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }


    /**
     * registerTemplateAction register actions by passing action config to ActionTemplate.
     *
     * @param string $actionTemplateName
     * @param array $templateArguments
     */
    public function registerTemplateAction($actionTemplateName, array $templateArguments)
    {
        $template = $this->generator->getTemplate($actionTemplateName);
        $template->register($this, $actionTemplateName, $templateArguments);
    }

    /**
     * register method registers the action class with specified action template name and its arguments
     *
     */
    public function register($targetActionClass, $actionTemplateName, array $templateArguments = array())
    {
        $this->pretreatments[$targetActionClass] = array(
            'template' => $actionTemplateName,
            'arguments' => $templateArguments,
        );
    }

    public function countOfPretreatments()
    {
        return count($this->pretreatments);
    }

    public function getPretreatments()
    {
        return $this->pretreatments;
    }

    public function getGenerator()
    {
        return $this->generator;
    }

    public function getPretreatment($actionClass)
    {
        if (isset($this->pretreatments[$actionClass])) {
            return $this->pretreatments[$actionClass];
        }
    }

    /**
     * Register the action class generator to the autoloader list. so we can
     * handle action class generation if the class is not found.
     */
    public function autoload()
    {
        // use throw and not to prepend
        spl_autoload_register(array($this,'load'), true, false);
    }

    public function __destruct()
    {
        spl_autoload_unregister(array($this,'load'));
    }
}
