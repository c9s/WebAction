<?php
namespace Kendo\Acl;
use Exception;

class RuleLoader
{
    public $rules = array();

    public $fallbackAllow = false;

    public function load($rule) {
        if( is_string($rule) ) {
            $class = str_replace('::','\\',$rule);
            if( ! class_exists($class,true) ) {
                throw new Exception("Rule class $rule not found.");
            }
            return $this->rules[] = new $class;
        } else {
            return $this->rules[] = $rule;
        }
    }

    public function authorize($role,$resource,$operation)
    {
        foreach( $this->rules as $rule ) {
            $result = $rule->authorize($role,$resource,$operation);
            if( $result === true ) {
                return true;
            } elseif( $result === false ) {
                return false;
            } elseif( $result === null ) {
                // continue
            }
        }
        return $this->fallbackAllow;
    }
}


