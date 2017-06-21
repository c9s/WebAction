<?php
namespace ProductBundle\Action;
use WebAction;
use ProductBundle\Model\ProductImage;
use ProductBundle\Model\Feature;
use ProductBundle\Model\Resource;
use ProductBundle\Model\FeatureRel;

class UpdateProduct extends \WebAction\RecordAction\UpdateRecordAction
{
    public $recordClass = 'ProductBundle\\Model\\Product';

    public $mixin;

    public function preinit()
    {
        $this->mixin = new ProductBaseMixin($this);
        $this->mixin->preinit();
    }

    public function schema()
    {
        $this->mixin->schema();
    }

    public function successMessage($ret)
    {
        return "Product {$this->record->name} updated.";
    }
}

