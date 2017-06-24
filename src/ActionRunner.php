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
    protected $debug;

    protected $currentUser;

    protected $serviceContainer;

    protected $loader;

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
    public function __construct(ActionLoader $loader, DefaultConfigurations $configuration = null)
    {
        parent::__construct();

        $this->loader = $loader;

        if (!$configuration) {
            $configuration = new DefaultConfigurations;
        }

        $this->serviceContainer = $configuration;
    }

    public function setDebug($debug = true)
    {
        $this->debug = $debug;
    }

    public function getLoader()
    {
        return $this->loader;
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
        if (!Utils::validateActionName($actionName)) {
            throw new InvalidActionNameException("Invalid action name: $actionName.");
        }

        // translate :: into php namespace
        $class = Utils::toActionClass($actionName);

        // register results into hash
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
            $this->loader->loadActionClass($class);

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
