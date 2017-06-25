<?php

namespace ProductBundle\Action;

use WebAction\Action;
use WebAction\RecordAction\BaseRecordAction;

class ProductBaseMixin
{
    /**
     * The object to mixin
     */
    public $action;

    public function __construct(BaseRecordAction $action)
    {
        $this->action = $action;
    }

    public function preinit()
    {
        /**
         * TODO:  Note that the self_key is pointing the related class currently.
         *        We want to make self_key to point the action record itself.
         */
        $this->action->nested = true;
        $this->action->relationships['product_categories']['renderable'] = false;
    }

    public function schema()
    {
        $this->action->useRecordSchema();
    }
}
