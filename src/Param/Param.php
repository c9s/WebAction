<?php
namespace WebAction\Param;

use CascadingAttribute;

use WebAction\ActionRequest;
use WebAction\MessagePool;
use WebAction\Action;
use WebAction\ValueType\BaseType;
use DateTime;
use InvalidArgumentException;
use Exception;
use LogicException;
use Maghead\Runtime\Model;
use Magsql\Raw;
use Closure;

use FormKit\Element\Div;
use FormKit\Widget\Label;

use Maghead\Utils;

class Param extends CascadingAttribute
{
    public static $supportedIsa = ['Int', 'Num', 'Str', 'Bool', 'Dir', 'DateTime', 'Timestamp', 'Ip', 'Ipv4', 'Ipv6', 'Path', 'Regex', 'Url', 'Email', 'Json'];

    /**
     * @var WebAction\Action action object referenece
     * */
    protected $action;

    /**
     * @var string action param name
     */
    public $name;

    /**
     * @var string action param type
     */
    public $isa;

    /**
     * @var boolean is a required column ?
     *
     * XXX: note this is defined in supportedAttributes
     */
    public $required;

    /* current value ? */
    public $value;

    /* valid values */
    public $validValues;

    /* valid pair values */
    public $validPairs;

    public $optionValues;

    /* default value */
    public $default;

    /* is immutable ? */
    public $immutable;

    /* refer class *? */
    public $refer;

    /* default render Widget */
    public $widgetClass = 'TextInput';

    /* default widget attributes */
    public $widgetAttributes = array();

    /* default widget namespace */
    public static $widgetNamespace = \FormKit\Widget::class;

    public $validator;

    protected $inflator;

    public function __construct($name, Action $action)
    {
        $this->name = $name;
        $this->action = $action;
        $this->build();
    }

    /**
     *
     */
    protected function build() { }

    public function preinit()
    {
    }


    public function init()
    {

    }

    public function run(ActionRequest $request)
    {

    }

    public function isa($isa)
    {
        $isa = ucfirst($isa);
        $this->isa = $isa;
        return $this;
    }

    public function immutable()
    {
        $this->immutable = true;
        return $this;
    }

    public function required()
    {
        $this->required = true;
        return $this;
    }

    public function defaultValue($value)
    {
        $this->default = $value;

        return $this;
    }

    public function label($label)
    {
        $this->label = $label;

        return $this;
    }


    public function validator($value)
    {
        $this->validator = $value;

        return $this;
    }


    public function inflator(callable $inflator)
    {
        $this->inflator = $inflator;

        return $this;
    }

    /**
     * Render action column as {Type}Widget, with extra options/attributes
     *
     *     $this->column('created_on')
     *         ->renderAs('DateInput', array( 'format' => 'yy-mm-dd' ))
     *
     * @param string $type       Widget type
     * @param array  $attributes
     *
     * @return self
     */
    public function renderAs($type, array $attributes = null)
    {
        $this->widgetClass = $type;
        if ($attributes) {
            $this->widgetAttributes = array_merge($this->widgetAttributes, $attributes);
        }

        return $this;
    }








    public function inflate($formValue)
    {
        if ($this->inflator) {
            return call_user_func($this->inflator, $formValue, $this, $this->action);
        }

        if ($this->isa) {
            $type = BaseType::create($this->isa);
            return $type->parse($formValue);
        }

        return $formValue;
    }




    /**
     * typeCastValue method cast the form values to the corresponding php runtime value type.
     *
     * @param string|mixed $formValue usually string. (derived from $_POST, $_GET)
     */
    public function typeCastValue($formValue)
    {
        if ($isa = $this->isa) {
        }
        return $formValue;
    }






    public function getLabel()
    {
        if ($this->label) {
            return _($this->label);
        }
        return ucfirst($this->name);
    }

    public function getDefaultValue()
    {
        if (is_callable($this->default)) {
            return call_user_func($this->default);
        }

        return $this->default;
    }

    public function setValue($value)
    {
        // TODO: check valid values here.
        $this->value = $value;
    }

    /**
     * getValidValues is currently used in widget attributes.
     */
    public function getValidValues()
    {
        if (is_callable($this->validValues)) {
            if ($this->validValues instanceof Closure) {
                $this->validValues->bindTo($this);
                return $this->validValues->__invoke();
            }
            return call_user_func($this->validValues);
        }



        return $this->validValues;
    }

    /**
     * Check if a value is in the valid values.
     */
    public function isValidValue($value)
    {
        $valids = $this->getValidValues();
        return in_array($value, $valids);
    }

    public function getOptionValues()
    {
        if (is_callable($this->optionValues)) {
            if ($this->optionValues instanceof Closure) {
                $this->optionValues->bindTo($this);
                return $this->optionValues->__invoke();
            }
            return call_user_func($this->optionValues);
        }
        return $this->optionValues;
    }


    /**
     * We dont save any value here,
     * The value's should be passed from outside.
     *
     * Supported validations:
     *   * required
     *
     * @param mixed $value
     *
     * @return array|true Returns error with message or true
     */
    public function validate(ActionRequest $request)
    {
        $value = $request->arg($this->name);

        /* if it's file type , should read from $_FILES , not from the args of action */
        // TODO: note, we should do this validation in File Param or Image Param
        if ($this->required) {
            if ($this instanceof FileParam) {
                if (! $request->file($this->name) && ! $request->param($this->name)) {
                    return $this->error(
                        $this->action->messagePool->translate('file.required', $this->getLabel())
                    );
                }
            } else {
                // We use '==' here because form values might be "" zero length string.
                if ($request->existsParam($this->name) && $request->param($this->name) == null && ! $this->default) {
                    return $this->error(
                        $this->action->messagePool->translate('param.required', $this->getLabel())
                    );
                }
            }
        }

        // isa should only work for non-null values.
        // empty string parameter is equal to null
        if ($this->isa && $request->existsParam($this->name)) {

            if ($value !== '' && $value !== null) {
                $type = BaseType::create($this->isa);
                if (false === $type->test($value)) {
                    return [false, "Invalid type value on {$this->isa}"];
                }
            }
        }

        if ($this->validator) {
            if ($this->validator instanceof Closure) {
                $this->validator->bindTo($this);
                return $this->validator($value, $request);
            }
            return call_user_func($this->validator, $value, $request);
        }

        // TODO: valid values validation

        return true;
    }

    protected function error($message)
    {
        return [false, $message];
    }

    protected function ok($message)
    {
        return [true, $message];
    }


    /*******************************************************************************
     * Widget/UI Related Methods
     ******************************************************************************/

    /**
     * Render current parameter column to HTML
     *
     * @param  array|null $attributes
     * @return string
     */
    public function render($attributes = null)
    {
        return $this->createWidget(null, $attributes)
            ->render();
    }

    public function createHintWidget($widgetClass = null, $attributes = array())
    {
        if ($this->hint) {
            $class = $widgetClass ?: Div::class;
            $widget = new $class($attributes);
            $widget->append($this->hint);

            return $widget;
        }
    }

    public function createLabelWidget($widgetClass = null, $attributes = array())
    {
        $class = $widgetClass ?: Label::class;
        if ($this->required) {
            return new $class('* ' . $this->getLabel());
        }
        return new $class($this->getLabel());
    }


    public function getRenderableCurrentValue()
    {
        // XXX: we should handle "false", "true", and "NULL"
        if ($this->value instanceof Model) {
            return $this->value->dataKeyValue();
        }
        if ($this->value instanceof Raw) {
            return null;
        }
        return $this->value;
    }

    /**
     * A simple widget factory for Action Param
     *
     * @param  string                    $widgetClass Widget Class.
     * @param  array                     $attributes  Widget attributes.
     * @return FormKit\Widget\BaseWidget
     */
    public function createWidget($widgetClass = null, $attributes = array())
    {
        $class = $widgetClass ?: $this->widgetClass;

        // convert attributes into widget style attributes
        $newAttributes = array();
        $newAttributes['label'] = $this->getLabel();

        if ($this->validValues) {
            $newAttributes['options'] = $this->getValidValues();
        } elseif ($this->optionValues) {
            $newAttributes['options'] = $this->getOptionValues();
        }

        if ($this->immutable) {
            $newAttributes['readonly'] = true;
        }

        // for inputs (except password input),
        // we should render the value (or default value)
        if (false === stripos($class, 'Password')) {
            // The Param class should respect the data type
            if ($this->value !== null) {
                if ($val = $this->getRenderableCurrentValue()) {
                    $newAttributes['value'] = $val;
                }
            } else if ($this->default) {
                $default = $this->getDefaultValue();
                if (!$default instanceof Raw) {
                    $newAttributes['value'] = $default;
                }
            }
        }

        if ($this->placeholder) {
            $newAttributes['placeholder'] = $this->placeholder;
        }
        if ($this->hint) {
            $newAttributes['hint'] = $this->hint;
        }

        if ($this->immutable) {
            $newAttributes['readonly'] = true;
        }

        // merge override attributes
        if ($this->widgetAttributes) {
            $newAttributes = array_merge($newAttributes, $this->widgetAttributes);
        }
        if ($attributes) {
            $newAttributes = array_merge($newAttributes, $attributes);
        }

        // if it's not a full-qualified class name
        // we should concat class name with default widget namespace
        if ('+' == $class[0]) {
            $class = substr($class, 1);
        } else {
            $class = \Maghead\Utils::resolveClass($class, [\App\Widget::class, \WebAction\Widget::class, static::$widgetNamespace], $this->action);
        }

        // create new widget object.
        return new $class($this->name, $newAttributes);
    }

}
