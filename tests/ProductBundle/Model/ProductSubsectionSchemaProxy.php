<?php
namespace ProductBundle\Model;

use LazyRecord;
use LazyRecord\Schema\RuntimeSchema;
use LazyRecord\Schema\Relationship;

class ProductSubsectionSchemaProxy extends RuntimeSchema
{

    public static $column_names = array (
  0 => 'title',
  1 => 'cover_image',
  2 => 'content',
  3 => 'product_id',
  4 => 'id',
);
    public static $column_hash = array (
  'title' => 1,
  'cover_image' => 1,
  'content' => 1,
  'product_id' => 1,
  'id' => 1,
);
    public static $mixin_classes = array (
);
    public static $column_names_include_virtual = array (
  0 => 'title',
  1 => 'cover_image',
  2 => 'content',
  3 => 'product_id',
  4 => 'id',
);

    const schema_class = 'ProductBundle\\Model\\ProductSubsectionSchema';
    const collection_class = 'ProductBundle\\Model\\ProductSubsectionCollection';
    const model_class = 'ProductBundle\\Model\\ProductSubsection';
    const model_name = 'ProductSubsection';
    const model_namespace = 'ProductBundle\\Model';
    const primary_key = 'id';
    const table = 'product_subsections';
    const label = 'ProductSubsection';

    public function __construct()
    {
        /** columns might have closure, so it can not be const */
        $this->columnData      = array( 
  'title' => array( 
      'name' => 'title',
      'attributes' => array( 
          'type' => 'varchar(64)',
          'isa' => 'str',
          'size' => 64,
          'label' => '子區塊標題',
          'renderAs' => 'TextInput',
          'widgetAttributes' => array( 
              'size' => 50,
            ),
        ),
    ),
  'cover_image' => array( 
      'name' => 'cover_image',
      'attributes' => array( 
          'type' => 'varchar(64)',
          'isa' => 'str',
          'size' => 64,
          'label' => '子區塊封面圖',
          'renderAs' => 'ThumbImageFileInput',
          'widgetAttributes' => array( 
            ),
        ),
    ),
  'content' => array( 
      'name' => 'content',
      'attributes' => array( 
          'type' => 'text',
          'isa' => 'str',
          'label' => '子區塊內文',
          'renderAs' => 'TextareaInput',
          'widgetAttributes' => array( 
              'class' => '+=mceEditor',
              'rows' => 5,
              'cols' => 50,
            ),
        ),
    ),
  'product_id' => array( 
      'name' => 'product_id',
      'attributes' => array( 
          'type' => 'integer',
          'isa' => 'int',
          'refer' => 'ProductBundle\\Model\\Product',
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
  'title',
  'cover_image',
  'content',
  'product_id',
);
        $this->primaryKey      = 'id';
        $this->table           = 'product_subsections';
        $this->modelClass      = 'ProductBundle\\Model\\ProductSubsection';
        $this->collectionClass = 'ProductBundle\\Model\\ProductSubsectionCollection';
        $this->label           = 'ProductSubsection';
        $this->relations       = array( 
  'product' => \LazyRecord\Schema\Relationship::__set_state(array( 
  'data' => array( 
      'type' => 4,
      'self_schema' => 'ProductBundle\\Model\\ProductSubsectionSchema',
      'self_column' => 'product_id',
      'foreign_schema' => 'ProductBundle\\Model\\ProductSchema',
      'foreign_column' => 'id',
    ),
)),
);
        $this->readSourceId    = 'default';
        $this->writeSourceId    = 'default';
        parent::__construct();
    }

    /**
     * Code block for message id parser.
     */
    private function __() {
        _('ProductSubsection');
        _('子區塊標題');
        _('子區塊封面圖');
        _('子區塊內文');
    }

}
