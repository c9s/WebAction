<?php
namespace WebAction;

use FormKit;
use WebAction\Param\Param;
use WebAction\Param\ImageParam;
use WebAction\Param\FileParam;
use WebAction\Result;
use WebAction\ActionRequest;
use WebAction\MessagePool;
use WebAction\Csrf\CsrfTokenProvider;
use WebAction\Csrf\CsrfToken;
use WebAction\DefaultConfigurations;
use WebAction\View\StackView;
use Universal\Http\HttpRequest;
use Universal\Http\FilesParameter;
use Exception;
use InvalidArgumentException;
use BadMethodCallException;
use ArrayAccess;
use IteratorAggregate;
use FormKit\Widget\HiddenInput;
use FormKit\Widget\SubmitInput;

class Action implements IteratorAggregate
{
    const moniker = 0;

    public static $defaultFieldView = 'WebAction\FieldView\DivFieldView';

    protected $currentUser;

    /**
     * @var Action parent action
     */
    protected $parent;

    public $nested = true;

    public $relationships = array();

    public $actionFieldName = '__action';

    /**
     * @var string the csrf token field name is used for rendering a hidden widget for csrf token.
     */
    public $csrfTokenFieldName = '__csrf_token';

    /**
     * @var array
     *
     * TODO: should be protected
     */
    public $args = array();   // post,get args for action


    protected $originalArgs = [];



    /**
     * @var array[Universal\Http\UploadedFile] the action wide file objects.
     */
    protected $uploadedFiles = array();

    /**
     * @var WebAction\Result
     */
    public $result; // action result

    /**
     * @var WebAction\Param[string Prama name]
     */
    public $params = array();


    /**
     * @var Universal\Http\HttpRequest request object
     */
    public $request;

    protected $currentRequest;

    /**
     * @var array filter out fields (blacklist)
     */
    public $filterOutFields;

    /**
     * @var array take these fields only.
     */
    public $takeFields;

    /**
     * @var boolean enable validatation ?
     */
    public $enableValidation = true;


    /**
     * @var array mix-in instances
     */
    public $mixins = array();


    /**
     * @var boolean Enable CSRF token
     *
     * A user class may override this property to disable/enable csrf token
     * verification.
     */
    protected $enableCSRFToken = false;


    /**
     * @var WebAction\CSRFTokenProvider
     */
    protected $csrf;


    /**
     * @var Pimple\Container
     */
    protected $services;


    /**
     * @var WebAction\MessagePool
     */
    public $messagePool;

    /**
     * Constructing Action objects
     *
     * @param array $args        The request arguments
     * @param mixed $options     Can be ArrayAccess or array
     */
    public function __construct(array $args = array(), $options = array())
    {
        // try to get service container or create a new one.
        // we use service container to get:
        //   1. MessagePool
        //   2. CsrfTokenProvider
        if (isset($options['services'])) {
            $this->services = $options['services'];
        } else {
            $this->services = new DefaultConfigurations;
        }

        if (isset($options['current_user'])) {
            $this->currentUser = $options['current_user'];
        } elseif (isset($this->services['current_user'])) {
            $this->currentUser = $this->services['current_user'];
        }

        if (isset($options['csrf'])) {
            $this->csrf = $options['csrf'];
        } elseif (isset($this->services['csrf'])) {
            $this->csrf = $this->services['csrf'];
        }


        if (isset($options['message_pool'])) {
            $this->messagePool = $options['message_pool'];
        } elseif (isset($this->services['message_pool'])) {
            $this->messagePool = $this->services['message_pool'];
        }

        // save parent action
        if (isset($options['parent'])) {
            $this->parent = $options['parent'];
        }

        // Conditions for setting up request object
        if (isset($options['request'])) {

            $this->request = $options['request'];

        } else if (isset($this->services['action_request'])) {

            // fallback to action_request defiend in service
            $this->request = $this->services['action_request'];

        } else if (isset($_FILES)) {

            // Universal\Http\HttpRequest already fixes the files array
            $this->request = new ActionRequest($args, $_FILES);

        } else {

            // When rendering Action with view, we probably won't have this request object.
            $this->request = new ActionRequest($args, []);
        }

        $this->result  = new Result;
        $this->mixins = $this->mixins();

        $this->preinit();
        foreach ($this->mixins as $mixin) {
            $mixin->preinit();
        }

        // initialize parameter objects
        $this->schema();

        // intiailize schema from mixin classes later,
        // so that we can use mixin to override the default options.
        foreach ($this->mixins as $mixin) {
            $mixin->schema();
        }

        $this->setupArguments($args);

        // action & parameters initialization
        // ===================================
        //
        // call the parameter preinit method to initialize
        // foreach is always faster than array_map
        foreach ($this->params as $param) {
            $param->preinit($this->args);
        }

        // call the parameter init method
        foreach ($this->params as $param) {
            $param->init($this->args);
        }
        // user-defined init script
        $this->init();

        $this->init();
    }

        // call the parameter init method
        foreach ($this->params as $param) {
            $param->postinit($this->args);
        }



        $this->postinit();
        foreach ($this->mixins as $mixin) {
            $mixin->postinit();
        }

        // save request arguments
        $this->result->args($this->args);
    }


    public function mixins()
    {
        return array(
            /* new MixinClass( $this, [params] ) */
        );
    }

    /**
     * Load values into the parameter columns.
     */
    protected function loadParamValues(array $args)
    {
        // load param values from $arguments
        $overlap = array_intersect_key($args, $this->params);
        foreach ($overlap as $name => $val) {
            $this->getParam($name)->value($val);
        }
    }


    /**
     * Rewrite param names with index, this method is for
     * related records. e.g.
     *
     * relationId[ index ][name] = value
     * relationId[ index ][column2] = value
     *
     * @param string $key
     * @param string $index The default index key for rendering field index name.
     *
     * @return string index number
     */
    public function setParamNamesWithIndex($key, $index = null)
    {
        // if the record is loaded, use the primary key as identity.
        // if not, use timestamp simply, hope seconds is enough.
        if (! $index) {
            $index = ($this->record && $this->record->id)
                ? $this->record->id
                : md5(microtime());
        }
        foreach ($this->params as $name => $param) {
            $param->name = sprintf('%s[%s][%s]', $key, $index, $param->name);
        }
        $this->actionFieldName = sprintf('%s[%s][%s]', $key, $index, $this->actionFieldName);
        return $index;
    }

    /**
     * Takes few fields only
     *
     * $this->takes('field1', 'field2');
     */
    public function takes($fields)
    {
        $args = func_get_args();
        if (count($args) > 1) {
            $this->takeFields = (array) $args;
        } else {
            $this->takeFields = (array) $fields;
        }

        return $this;
    }

    protected function inflateArguments(array $args)
    {
        $newArgs = [];

        foreach ($args as $name => $value) {
            if ($param = $this->getParam($name)) {
                $newArgs[$name] = $param->inflate($value);
            } else {
                $newArgs[$name] = $value;
            }
        }

        return $newArgs;
    }

    /**
     * Apply arguments whitelist (takeFields) and blacklist (filterOutFields)
     *
     * @return args
     */
    protected function filterArguments(array $args)
    {
        // find immutable params and unset them
        foreach ($this->params as $name => $param) {
            // should n't unset values if we are going to create
            // this method will be overrided in CreateRecordAction
            if ($param->immutable) {
                unset($args[$name]);
            }
        }

        if ($this->takeFields) {
            // take these fields only
            return array_intersect_key($args, array_fill_keys($this->takeFields, 1));
        } elseif ($this->filterOutFields) {
            return array_diff_key($args, array_fill_keys($this->filterOutFields, 1));
        }
        return $args;
    }

    /**
     * For Schema, Setup filter out fields,
     * When filterOut fields is set,
     * Action will filter out those columns when executing action
     * Action View will skip rendering these column
     *
     * @param array $fields Field names
     */
    public function filterOut($fields)
    {
        $args = func_get_args();
        if (count($args) > 1) {
            $this->filterOutFields = (array) $args;
        } else {
            $this->filterOutFields = (array) $fields;
        }

        return $this;
    }

    public function invalidField($n, $message, $desc = null)
    {
        $this->result->addValidation($n, array(
            'valid' => false,
            'message' => $message,
            'desc' => $desc,
            'field' => $n,
        ));
    }

    public function requireArg($n)
    {
        $v = $this->arg($n);
        if ($v === null || $v === "") {
            $param = $this->getParam($n);
            $this->result->addValidation($n, array(
                'valid' => false,
                'message' => $this->messagePool->translate('param.required', $param ? $param->getLabel() : $n),
                'field' => $n,
            ));
            return false;
        }
        return true;
    }

    public function requireArgs()
    {
        $ns = func_get_args();
        $satisfied = true;
        foreach ($ns as $n) {
            if (false === $this->requireArg($n)) {
                $satisfied = false;
            }
        }
        return $satisfied;
    }



    /**
     * Run parameter validator to validate argument.
     *
     * @param string $name is a parameter name
     */
    public function validateParam($name)
    {
        // skip __ajax_request field
        if ($name === '__ajax_request') {
            return true;
        }

        if (! isset($this->params[ $name ])) {
            return true;

            // just skip it.
            $this->result->addValidation($name, array(
                'valid' => false,
                'message' => "Contains invalid arguments: $name",
                'field' => $name,
            ));
            return true;
        }

        $param = $this->params[ $name ];

        /*
         * $ret contains:
         *
         *    [ boolean pass, string message ]
         *
         * or
         *
         *    [ boolean pass ]
         */
        $ret = (array) $param->validate($this->arg($name));
        if (is_array($ret)) {
            if ($ret[0]) { // success
                # $this->result->addValidation( $name, array( "valid" => $ret[1] ));
            } else {
                $this->result->addValidation($name, array(
                    'valid' => false,
                    'message' => @$ret[1],
                    'field' => $name,
                ));  // $ret[1] = message
                return false;
            }
        } else {
            throw new \Exception("Unknown validate return value of $name => " . $this->getName());
        }
        return true;
    }

    /**
     * Run validates
     *
     * Foreach parameters, validate the parameter through validateParam method.
     *
     * @return bool pass flag, returns FALSE on error.
     */
    public function runValidate()
    {
        /* it's different behavior when running validation for create,update,delete,
         *
         * for generic action, just traverse all params. */
        $foundError = false;
        foreach ($this->params as $name => $param) {
            if (false === $this->validateParam($name)) {
                $foundError = true;
            }
        }

        // we do this here because we need to validate all param(s)
        if ($foundError) {
            $this->result->error($this->messagePool->translate('validation.error'));
            return false;
        }

        // OK
        return true;
    }

    public function isAjax()
    {
        return isset($_REQUEST['__ajax_request']);
    }


    /**
     * Disable CsrfTokenProtection
     */
    public function disableCsrfTokenProtection()
    {
        $this->enableCSRFToken = false;
    }


    /**
     * Return the current request object.
     *
     * @return ActionRequest
     */
    final public function getCurrentRequest()
    {
        return $this->currentRequest;
    }


    /**
     * Invoke is a run method wraper
     */
    final public function handle(ActionRequest $request)
    {
        // TODO: use the args here to run the action
        $args = $request->getArguments();
        $this->currentRequest = $request;



        if (session_id() && $this->csrf && $this->enableCSRFToken) {
            // read csrf token from __csrf_token field or _csrf_token field
            $insecureToken = $this->arg($this->csrfTokenFieldName) ?: $this->arg('_csrf_token'); // _csrf_token is for backward compatibility

            // or we can read the csrf token from http header
            if (!$insecureToken) {
                if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                    $insecureToken = $_SERVER['HTTP_X_CSRF_TOKEN'];
                }
            }

            // if we still don't get the csrf token
            if (!$insecureToken) {
                // $this->result->error('CSRF token is invalid: empty token given.');
                $errorMsg = $this->messagePool->translate('csrf.token_invalid');
                $this->result->error($errorMsg, 401);
                $this->result['csrf_token_invalid'] = true;
                return false;
            }

            if (!$this->csrf->isValidToken($insecureToken, $_SERVER['REQUEST_TIME'])) {
                $errorMsg = $this->messagePool->translate('csrf.token_mismatch');
                $this->result->error($errorMsg, 401);
                $this->result['csrf_token_mismatch'] = true;
                return false;
            }
        }

        $user = $this->getCurrentUser();
        $result =  $this->currentUserCan($user, 'run', $this->args);
        if (is_array($result)) {
            if (!$result[0]) {
                $this->result->error($result[1]);
                return false;
            }
        } elseif (!$result) {
            return false;
        }

        if ($this->enableValidation && false === $this->runValidate()) {  // if found error, return true;
            return false;
        }

        /* run column methods */
        // XXX: merge them all...
        $this->beforeRun();
        foreach ($this->mixins as $mixin) {
            $mixin->beforeRun();
        }

        if (false === $this->run()) {
            return false;
        }

        foreach ($this->mixins as $mixin) {
            if (false === $mixin->run()) {
                return false;
            }
        }

        if (false === $this->afterRun()) {
            return false;
        }
        foreach ($this->mixins as $mixin) {
            if (false === $mixin->afterRun()) {
                return false;
            }
        }
        return true;
    }

    public function __invoke()
    {
        return $this->handle($this->request);
    }


    /* **** value getters **** */

    /**
     * Get Action name
     *
     * @return string
     */
    public function getName()
    {
        $sig = $this->getSignature();
        $pos = strpos($sig, '::Action::');
        return $pos ? substr($sig, $pos + strlen('::Action::')) : $sig;
    }

    public function params($all = false)
    {
        return $this->getParams($all);
    }

    public function getParams($all = false)
    {
        $self = $this;
        if ($all) {
            return $this->params;
        }
        if ($this->takeFields) {
            return array_intersect_key($this->params, array_fill_keys($this->takeFields, 1));  // find white list
        } elseif ($this->filterOutFields) {
            return array_diff_key($this->params, array_fill_keys($this->filterOutFields, 1)); // diff keys by blacklist
        }

        return $this->params;
    }

    public function getParam($field)
    {
        return isset($this->params[ $field ])
                ? $this->params[ $field ]
                : null;
    }

    public function hasParam($field)
    {
        return isset($this->params[ $field ]);
    }

    public function removeParam($field)
    {
        if (isset($this->params[$field])) {
            $param = $this->params[$field];
            unset($this->params[$field]);
            return $param;
        }
    }


    /**
     * Return column widget object
     *
     * @param string $field field name
     *
     * @return FormKit\Widget
     */
    public function widget($field, $widgetClass = null)
    {
        return $this->getParam($field)->createWidget($widgetClass);
    }


    /**
     * Create and get displayable widgets
     *
     * @param boolean $all get all parameters ? or filter paramemters
     */
    public function getWidgets($all = false)
    {
        $widgets = array();
        foreach ($this->getParams($all) as $param) {
            $widgets[] = $param->createWidget();
        }

        return $widgets;
    }


    public function getWidgetsByNames($names, $all = false)
    {
        $widgets = array();
        foreach ($names as $name) {
            if ($param = $this->getParam($name)) {
                $widgets[] = $param->createWidget();
            }
        }

        return $widgets;
    }

    /**
     * Get current user
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }


    /**
     * Set current user
     *
     * @param mixed Current user object.
     */
    public function setCurrentUser($user)
    {
        $this->currentUser = $user;
    }


    /**
     * Pass current user object to check permission.
     *
     * @return bool
     */
    public function currentUserCan($user, $right, $args = array())
    {
        return $this->record ? $this->record->currentUserCan($this->type, $args, $user) : true;
    }



    /**
     * Set/Get argument
     *
     * @param string $name  Argument key
     * @param mixed  $value (optional)
     *
     * @return mixed Argument value
     */
    public function arg($name)
    {
        $args = func_get_args();
        $nOfArgs = count($args);

        // getting values
        if (1 === $nOfArgs) {
            if (array_key_exists($name, $this->args)) {
                return $this->args[$name];
            }
            return null;
        } elseif (2 === $nOfArgs) {
            // set value
            return $this->args[ $name ] = $args[1];
        } else {
            throw new InvalidArgumentException("arg() method only allows setting value by 2 arguments. getting value by 1 argument.");
        }
    }

    public function defined($name)
    {
        return isset($this->args[$name]);
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * TODO: we should use the file payload from Universal\Http\HttpRequest.
     *
     * @return array
     */
    public function file($name)
    {
        return $this->request->file($name);
    }

    public function hasFile($name)
    {
        return $this->request->file($name) ? true : false;
    }

    /**
     * Set argument
     *
     * @param string $name  argument key.
     * @param mixed  $value argument value.
     *
     * @return this
     */
    protected function setArgument($name, $value)
    {
        $this->args[ $name ] = $value;
        return $this;
    }

    protected function setArguments(array $args)
    {
        $this->args = $args;
        return $this;
    }

    protected function removeArgument($name)
    {
        unset($this->args[$name]);
        return $this;
    }


    protected function setArg($name, $value)
    {
        $this->args[ $name ] = $value ;
        return $this;
    }

    /**
     * Set arguments
     *
     * @param array
     */
    protected function setArgs(array $args)
    {
        $this->args = $args;
        return $this;
    }


    protected function mergeArgs(array $args)
    {
        $this->args = array_merge($this->args, $args);
        return $this;
    }


    /**
     * Define a param object from Action,
     *
     * Note: when using this method, a param that is already
     * defined will be overrided.
     *
     * TODO: add 'replaceParam' to replace a param object, 'param' should just
     * return the original param object.
     *
     * @param string $field      Field name
     * @param string $paramType  Field Type (will be Param Type)
     *
     * @return WebAction\Param
     *
     *     $this->param('username'); // use WebAction\Param
     *     $this->param('file', 'file' ); // use WebAction\Param\File
     *     $this->param('image', 'image' ); // use WebAction\Param\Image
     *
     */
    public function param($field, $paramType = null)
    {
        if (isset($this->params[$field]) && $paramType != null) {
            throw new Exception(get_class($this) . ': You were trying to set param type on an existed param object. Please use "replaceParam" method instead.');
        }

        if (isset($this->params[$field])) {
            return $this->params[$field];
        }
        return $this->replaceParam($field, $paramType);
    }


    /**
     *
     * @param string $field      Field name
     * @param string $paramType  Field Type (will be Param Type)
     *
     * @return WebAction\Param
     */
    public function replaceParam($field, $paramType = null)
    {
        if ($paramType) {
            $class = ($paramType[0] !== '+')
                ? 'WebAction\\Param\\' . ucfirst($paramType) . 'Param'
                : substr($paramType, 1);
        } else {
            $class = 'WebAction\\Param\\Param';
        }
        if (! class_exists($class, true)) { // trigger spl class autoloader to load class file.
            throw new Exception("Action param($field): column class $class not found.");
        }
        return $this->params[$field] = new $class($field, $this);
    }

    /**
     * Return the description of this action class, `description` method
     * returns the human readable description for logging.
     *
     * by default it returns the class name of current instance.
     *
     * @return string
     */
    public function description()
    {
        return get_class($this);
    }


    /**
     * Action schema is defined here.
     */
    public function schema()
    {
    }

    public function preinit()
    {
    }

    public function init()
    {
    }

    public function postinit()
    {
    }



    /**
     * Add data to result object
     *
     * @param string $key
     * @param mixed  $val
     */
    public function addData($key, $val)
    {
        $this->result->addData($key, $val);
    }


    public function beforeRun()
    {
    }

    public function afterRun()
    {
    }

    /**
     * Run method, defined by user, contains the main logics of the action.
     */
    public function run()
    {
        return true;
    }


    /**
     * Complete action field
     *
     * @param string $field field name
     * */
    public function complete($field)
    {
        $param = $this->getParam($field);
        if (! $param) {
            die('action param not found.');
        }
        $ret = $param->complete();
        if (! is_array($ret)) {
            throw new Exception("Completer doesnt return array. [type,list]\n");
        }

        // [ type , list ]
        $this->result->completion($field, $ret[0], $ret[1]);
    }

    /**
     * Returns Action result, result is empty before running.
     *
     * @return WebAction\Result
     */
    public function getResult()
    {
        return $this->result;
    }


    /**
     * Redirect
     *
     * @param string $path
     */
    public function redirect($path)
    {

        /* for ajax request, we should redirect by json result,
         * for normal post, we should redirect directly. */
        if ($this->isAjax()) {
            $this->result->redirect($path);

            return;
        } else {
            header('Location: ' . $path);
            exit(0);
        }
    }


    /**
     * Redirect to path with a delay
     *
     * @param string  $path
     * @param integer $secs
     */
    public function redirectLater($path, $secs = 1)
    {
        if ($this->isAjax()) {
            $this->result->redirect($path, $secs);
            return;
        } else {
            header("Refresh: $secs; url=$path");
        }
    }

    /**
     * Create an Action View instance for Action.
     *
     *      ->asView()
     *      ->asView('ViewClass')
     *      ->asView(array( .. view options ..))
     *      ->asView('ViewClass', array( .. view options ..))
     *
     * @param string $class      View class
     * @param array  $attributes View options
     *
     * @return WebAction\View\BaseView View object
     */
    public function asView()
    {
        $options = array();

        // built-in action view class
        $class = StackView::class;
        $args = func_get_args();

        // got one argument
        if (count($args) < 2 and isset($args[0])) {
            if (is_string($args[0])) {
                $class = $args[0];
            } elseif (is_array($args[0])) {
                $options = $args[0];
            }
        } elseif (count($args) == 2) {
            $class = $args[0];
            $options = $args[1];
        }
        return new $class($this, $options);
    }



    /**
     * Get action signature, this signature is for dispatching
     *
     * @return string Signature string
     */
    public function getSignature()
    {
        return str_replace('\\', '::', get_class($this));
    }


    /**
     * Render widget
     *
     * @param  string $name  column name
     * @param  string $type  Widget type, Input, Password ... etc
     * @param  array  $attrs Attributes
     * @return string HTML string
     */
    public function renderWidget($name, $type = null, $attrs = array())
    {
        return $this->getParam($name)->createWidget($type, $attrs)->render();
    }



    /**
     * Render column with field view class
     *
     * renderField( 'name' )
     * renderField( 'name', FieldViewClass , WidgetAttributes )
     * renderField( 'name', WidgetAttributes )
     *
     * @param string $name           column name
     * @param string $fieldViewClass
     * @param array  $attrs
     */
    public function renderField($name)
    {
        // the default field view class.
        $args = func_get_args();
        $fieldViewClass = self::$defaultFieldView;
        $attrs = array();
        if (count($args) == 2) {
            if (is_string($args[1])) {
                $fieldViewClass = $args[1];
            } elseif (is_array($args[1])) {
                $attrs = $args[1];
            }
        } elseif (count($args) == 3) {
            if ($args[1]) {
                $fieldViewClass = $args[1];
            }
            if ($args[2]) {
                $attrs = $args[2];
            }
        }
        $param = $this->getParam($name);
        if (! $param) {
            throw new Exception("Action param '$name' is not defined.");
        }
        $view = new $fieldViewClass($param);
        $view->setWidgetAttributes($attrs);

        return $view->render();
    }


    /**
     * Render the label of a action parameter
     *
     * @param string $name  parameter name
     * @param array  $attrs
     */
    public function renderLabel($name, $attrs = array())
    {
        $label = $this->getParam($name)->createLabelWidget();

        return $label->render($attrs);
    }


    /**
     * A quick helper for rendering multiple fields
     *
     * @param  string[] $fields Field names
     * @return string   HTML string
     */
    public function renderWidgets(array $fields, $type = null, $attributes = array())
    {
        $html = '';
        foreach ($fields as $field) {
            $html .= $this->getParam($field)->render(null, $attributes) . "\n";
        }

        return $html;
    }

    /**
     * Render submit button widget
     *
     * @param  array  $attrs Attributes
     * @return string HTML string
     */
    public function renderSubmitWidget(array $attrs = array())
    {
        $submit = new SubmitInput;

        return $submit->render($attrs);
    }



    /**
     * Render Button wigdet HTML
     *
     * @param  array  $attrs Attributes
     * @return string HTML string
     */
    public function renderButtonWidget(array $attrs = array())
    {
        $button = new FormKit\Widget\ButtonInput;
        return $button->render($attrs);
    }



    /**
     * Shortcut method for creating signature widget
     */
    public function createSignatureWidget()
    {
        return new HiddenInput($this->actionFieldName, array( 'value' => $this->getSignature()));
    }

    /**
     * Render action hidden field for signature
     *
     *      <input type="hidden" name="action" value="User::Action::UpdateUser"/>
     *
     * @return string Hidden input HTML
     */
    public function renderSignatureWidget(array $attrs = array())
    {
        $hidden = $this->createSignatureWidget();
        return $hidden->render($attrs);
    }

    /**
     * Get the current CSRF token in the session
     *
     * @return string CSRF token string
     */
    public function getCSRFToken()
    {
        // TODO support loading csrf token from session or header "X-CSRF-TOKEN"
        if ($this->csrf) {
            $token = $this->csrf->loadCurrentToken();
            if ($token == null || $token->isExpired($_SERVER['REQUEST_TIME'])) {
                $token = $this->csrf->generateToken();
            }
            return $token->hash;
        }
        return null;
    }


    /**
     * Render action hidden field for csrf token
     *
     *      <input type="hidden" name="__csrf_token" value="NGE1YWQ4N2I5MTRjMjYzZTkxZGY3MmJhYjVkODE0ZmIyMmNiYzk1MA=="/>
     *
     * @return string Hidden input HTML
     */
    public function renderCSRFTokenWidget(array $attrs = array())
    {
        // Create csrf token widget only when csrf provider is defined and enableCSRFToken is on.
        if (!$this->csrf) {
            throw new Exception('csrf token provider is not provided.');
        }
        $hash = $this->getCSRFToken();
        if ($hash) {
            $hidden = new HiddenInput($this->csrfTokenFieldName, array( 'value' => $hash ));
            return $hidden->render($attrs);
        }

        return null;
    }

    /**
     * Render a field or render all fields,
     *
     * Note: this was kept for old version templates.
     *
     * @param  string $name  field name (optional, when omit this, Action renders all fields)
     * @param  array  $attrs field attributes
     * @return string HTML string
     */
    public function render($name = null, $attrs = array())
    {
        if ($name) {
            if ($widget = $this->widget($name)) {
                return $widget->render($attrs);
            }

            throw new Exception("Param '$name' is not defined.");

        } else {

            // render all widgets
            $html = '';
            foreach ($this->params as $param) {
                $html .= $param->render($attrs);
            }

            return $html;
        }
    }

    public function __set($name, $value)
    {
        if ($param = $this->getParam($name)) {
            $param->value = $value;
        } else {
            throw new InvalidArgumentException("Parameter $name not found.");
        }
    }

    public function __isset($name)
    {
        return isset($this->params[$name]);
    }

    public function __get($name)
    {
        return $this->getParam($name);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->params);
    }

    public function getMoniker()
    {
        return static::moniker;
    }

    /**
     * Report success
     *
     * @param string $message Success message
     * @param mixed  $data
     *
     * @return true
     */
    protected function success($message, array $data = null)
    {
        $this->result->success($message);
        if ($data) {
            $this->result->mergeData($data);
        }
        return true;
    }

    /**
     * Report error
     *
     * @param string $message Error message
     *
     * @return false
     */
    protected function error($message, array $data = null)
    {
        $this->result->error($message);
        if ($data) {
            $this->result->mergeData($data);
        }
        return false;
    }

    public function __call($m, $args)
    {
        foreach ($this->mixins as $mixin) {
            if (method_exists($mixin, $m)) {
                return call_user_func_array(array($mixin,$m), $args);
            }
        }
    }

    /**
     * This is a simple uploaded file storage, it doesn't support multiple files
     */
    public function uploadedFile($fieldName, $index = 0)
    {
        if (isset($this->uploadedFiles[$fieldName][$index])) {
            return $this->uploadedFiles[$fieldName][$index];
        }
    }

    public function saveUploadedFile($fieldName, $index, $file)
    {
        return $this->uploadedFiles[$fieldName][$index] = $file;
    }
}
