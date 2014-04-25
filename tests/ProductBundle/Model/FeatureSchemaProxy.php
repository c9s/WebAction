<?php
namespace ProductBundle\Model;

use LazyRecord;
use LazyRecord\Schema\RuntimeSchema;
use LazyRecord\Schema\Relationship;

class FeatureSchemaProxy extends RuntimeSchema
{

    public static $column_names = array (
  0 => 'name',
  1 => 'description',
  2 => 'image',
  3 => 'id',
);
    public static $column_hash = array (
  'name' => 1,
  'description' => 1,
  'image' => 1,
  'id' => 1,
);
    public static $mixin_classes = array (
);
    public static $column_names_include_virtual = array (
  0 => 'name',
  1 => 'description',
  2 => 'image',
  3 => 'id',
);

    const schema_class = 'ProductBundle\\Model\\FeatureSchema';
    const collection_class = 'ProductBundle\\Model\\FeatureCollection';
    const model_class = 'ProductBundle\\Model\\Feature';
    const model_name = 'Feature';
    const model_namespace = 'ProductBundle\\Model';
    const primary_key = 'id';
    const table = 'product_features';
    const label = 'Feature';

    public function __construct()
    {
        /** columns might have closure, so it can not be const */
        $this->columnData      = array( 
  'name' => array( 
      'name' => 'name',
      'attributes' => array( 
          'type' => 'varchar(128)',
          'isa' => 'str',
          'size' => 128,
          'label' => '產品功能名稱',
        ),
    ),
  'description' => array( 
      'name' => 'description',
      'attributes' => array( 
          'type' => 'text',
          'isa' => 'str',
          'label' => 'Description',
        ),
    ),
  'image' => array( 
      'name' => 'image',
      'attributes' => array( 
          'type' => 'varchar(250)',
          'isa' => 'str',
          'size' => 250,
          'label' => '產品功能圖示',
        ),
    ),
  'id' => array( 
      'name' => 'id',
      'attributes' => array( 
          'type' => 'integer',
          'isa' => 'int',
          'primary' => true,
          'autoIncrement' => true,
        ),
    ),
);
        $this->columnNames     = array( 
  'id',
  'name',
  'description',
  'image',
);
        $this->primaryKey      = 'id';
        $this->table           = 'product_features';
        $this->modelClass      = 'ProductBundle\\Model\\Feature';
        $this->collectionClass = 'ProductBundle\\Model\\FeatureCollection';
        $this->label           = 'Feature';
        $this->relations       = array( 
);
        $this->readSourceId    = 'default';
        $this->writeSourceId    = 'default';
        parent::__construct();
    }

    /**
     * Code block for message id parser.
     */
    private function __() {
        _('Feature');
        _('產品功能名稱');
        _('Description');
        _('產品功能圖示');
    }

}
