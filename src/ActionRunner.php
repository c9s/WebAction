<?php
namespace WebAction;

use Exception;
use IteratorAggregate;
use ArrayAccess;
use WebAction\Utils;
use WebAction\ActionRequest;
use WebAction\ActionLogger;
use WebAction\Exception\InvalidActionNameException;
use WebAction\Exception\ActionNotFoundException;
use WebAction\Exception\UnableToWriteCacheException;
use WebAction\Exception\UnableToCreateActionException;
use WebAction\Loggable;
use Closure;

/**
 * Run actions!
 *
 *
 *      full-qualified action name in web form:
 *              MyApp::Action::Login
 *              Phifty::Action::Login
 *      names like "Login", "Signup" should refer to
 *              {App}::Action::Login or
 *              {App}::Action::Signup
 *
 *  $runner = WebAction\ActionRunner::getInstance();
 *  $result = $runner->run();
 *  if ($result) {
 *      if ( $runner->isAjax() ) {
 *          echo $result;
 *      }
 *  }
 *
 * Iterator support:
 *
 *  foreach ($runner as $name => $result) {
 *
 *  }
 *
 */
use ArrayObject;

class ActionRunner extends ArrayObject
{
    protected $pretreatments = array();

    /**
     * @var array Result pool
     */
    protected $results = [];

    public $generator;

    public $cacheDir;

    protected $debug;

    protected $currentUser;

    protected $serviceContainer;

    /**
     * @param array $options
     *
     * Options:
     *
     *   'locale': optional, the current locale
     *   'cache_dir': optional, the cache directory of generated action classes
     *   'generator': optional, the customized Generator object.
     *
     */
    public function __construct($options = array())
    {
        parent::__construct();

        if ($options instanceof ServiceContainer) {

            // the cache_dir option is optional. if user provides one, we should use it.
            $this->cacheDir = $options['cache_dir'];
            $this->generator = $options['generator'];
            $this->serviceContainer = $options;
        } else {
            $this->serviceContainer = new ServiceContainer;

            // Default initializor
            if (isset($options['cache_dir'])) {
                $this->cacheDir = $options['cache_dir'];
            } else {
                $this->cacheDir = $this->serviceContainer['cache_dir'];
            }

            if (isset($options['generator'])) {
                $this->generator = $options['generator'];
            } else {
                $this->generator = $this->serviceContainer['generator'];
            }
        }

        if ($this->cacheDir && ! file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function setDebug($debug = true)
    {
        $this->debug = $debug;
    }


    public function getGenerator()
    {
        return $this->generator;
    }

    /**
     * Check if action request, then dispatch the action class.
     *
     *
     * @param string  $actionName
     * @param array   $arguments
     * @return WebAction\Result result array if there is such an action.
     * */
    public function run($actionName, array $arguments = array(), ActionRequest $request = null)
    {
        if (! Utils::validateActionName($actionName)) {
            throw new InvalidActionNameException("Invalid action name: $actionName.");
        }

        /* translate :: into php namespace */
        $class = Utils::toActionClass($actionName);

        /* register results into hash */
        $action = $this->createAction($class, $arguments, $request);
        $action->invoke();

        if (isset($this->serviceContainer['action_logger']) && $action instanceof Loggable) {
            $logger = $this->serviceContainer['action_logger'];

            // how do we call the logger?
            if ($logger instanceof Closure) {
                $logger($action);
            } elseif ($logger instanceof ActionLogger) {
                $logger->log($action);
            }
        }
        if ($moniker = $action->getMoniker()) {
            return $this[$moniker] = $action->getResult();
        }
        return $this[ $actionName ] = $action->getResult();
    }


    public function runWithRequest(ActionRequest $request)
    {
        if (!$request->getActionName()) {
            throw new InvalidActionNameException("");
        }
        if (! Utils::validateActionName($request->getActionName())) {
            throw new InvalidActionNameException("Invalid action name: " . $request->getActionName() . ".");
        }
        return $this->run($request->getActionName(), $request->getArguments(), $request);
    }


    /**
     * Run action request with a try catch block
     * return ajax response when __ajax_request is defined.
     *
     * @param resource $stream STDIN, STDOUT, STDERR, or any resource
     * @param array $arguments Usually $_REQUEST array
     * @param array $files  Usually $_FILES array
     * @return return true if it's an ajax response
     */
    public function handleWith($stream, array $arguments = array(), array $files = array())
    {
        try {
            $request = new ActionRequest($arguments, $files);
            $result = $this->runWithRequest($request);
            if ($result && $request->isAjax()) {
                if ($result->responseCode) {
                    http_response_code($result->responseCode);
                }

                // Deprecated:
                // The text/plain seems work for IE8 (IE8 wraps the
                // content with a '<pre>' tag.
                @header('Cache-Control: no-cache');
                @header('Content-Type: text/plain; Charset=utf-8');
                // Since we are using "textContent" instead of "innerHTML" attributes
                // we should output the correct json mime type.
                // header('Content-Type: application/json; Charset=utf-8');
                fwrite($stream, $result->__toString());
                return true;
            }
        } catch (Exception $e) {
            @header('HTTP/1.1 403 Action API Error');
            if ($request->isAjax()) {
                if (1 || $this->debug) {
                    // $trace = debug_backtrace();
                    fwrite($stream, json_encode(array(
                        'error'     => 1,
                        'message'   => $e->getMessage(),
                        'line'      => $e->getLine(),
                        'file'      => $e->getFile(),
                        'trace' => $e->getTraceAsString(),
                    )));
                } else {
                    fwrite($stream, json_encode(array(
                        'error' => 1,
                        'message' => $e->getMessage(),
                    )));
                }
                return true;
            } else {
                throw $e;
            }
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
    public function generateActionClass($class, array $pretreatment = null)
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
     * loadActionClass trigger the action class generation if the class doesn't
     * exist and loads the action class.
     *
     * @param string $class action class
     */
    public function loadActionClass($class)
    {
        if (!isset($this->pretreatments[$class])) {
            return false;
        }

        $pretreatment = $this->pretreatments[$class];
        if ($this->loadClassCache($class, $pretreatment['arguments'])) {
            return true;
        }

        $generatedAction = $this->generateActionClass($class, $pretreatment);
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

    public function registerAutoloader()
    {
        // use throw and not to prepend
        spl_autoload_register(array($this,'loadActionClass'), true, false);
    }

    public function __destruct()
    {
        spl_autoload_unregister(array($this,'loadActionClass'));
    }


    /**
     * registerAction register actions by passing action config to ActionTemplate.
     *
     * @param string $actionTemplateName
     * @param array $templateArguments
     */
    public function registerAction($actionTemplateName, array $templateArguments)
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

    public function getActionPretreatment($actionClass)
    {
        if (isset($this->pretreatments[$actionClass])) {
            return $this->pretreatments[$actionClass];
        }
    }

    public function isInvalidActionName($actionName)
    {
        return preg_match('/[^A-Za-z0-9:]/i', $actionName);
    }

    /**
     * Create action object from REQUEST
     *
     * @param string $class
     */
    public function createAction($class, array $args = array(), ActionRequest $request = null)
    {
        // Try to load the user-defined action
        if (!class_exists($class, true)) {

            // load the generated action
            $this->loadActionClass($class);

            // Check the action class existence
            if (! class_exists($class, true)) {
                throw new ActionNotFoundException("Action class not found: $class, you might need to setup action autoloader");
            }
        }
        $a = new $class($args, [
            'request'  => $request,
            'services' => $this->serviceContainer,
        ]);
        $a->setCurrentUser($this->currentUser);
        return $a;
    }

    public function setCurrentUser($user)
    {
        $this->currentUser = $user;
    }

    /**
     * Get all results
     *
     * @return WebAction\Result[]
     */
    public function getResults()
    {
        return $this->getArrayCopy();
    }

    /**
     * Get Action result by action name
     *
     * @param string $name action name (format: App::Action::ActionName)
     */
    public function getResult($name)
    {
        return isset($this[ $name ]) ?
                $this[ $name ] : null;
    }

    /**
     * Check if we have action result
     *
     * @param string $name Action name
     */
    public function hasResult($name)
    {
        return isset($this[$name]);
    }

    public function setResult($name, $result)
    {
        $this[$name] = $result;
    }

    public function removeResult($name)
    {
        unset($this[$name]);
    }

    public static function getInstance()
    {
        static $self;
        if ($self) {
            return $self;
        }
        return $self = new static;
    }
}
