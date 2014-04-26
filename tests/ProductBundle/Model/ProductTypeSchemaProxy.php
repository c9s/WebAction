<?php
namespace ProductBundle\Model;

use LazyRecord;
use LazyRecord\Schema\RuntimeSchema;
use LazyRecord\Schema\Relationship;

class ProductTypeSchemaProxy extends RuntimeSchema
{

    public static $column_names = array (
  0 => 'product_id',
  1 => 'name',
  2 => 'quantity',
  3 => 'comment',
  4 => 'id',
);
    public static $column_hash = array (
  'product_id' => 1,
  'name' => 1,
  'quantity' => 1,
  'comment' => 1,
  'id' => 1,
);
    public static $mixin_classes = array (
);
    public static $column_names_include_virtual = array (
  0 => 'product_id',
  1 => 'name',
  2 => 'quantity',
  3 => 'comment',
  4 => 'id',
);

    const schema_class = 'ProductBundle\\Model\\ProductTypeSchema';
    const collection_class = 'ProductBundle\\Model\\ProductTypeCollection';
    const model_class = 'ProductBundle\\Model\\ProductType';
    const model_name = 'ProductType';
    const model_namespace = 'ProductBundle\\Model';
    const primary_key = 'id';
    const table = 'product_types';
    const label = '產品類型';

    public function __construct()
    {
        /** columns might have closure, so it can not be const */
        $this->columnData      = array( 
  'product_id' => array( 
      'name' => 'product_id',
      'attributes' => array( 
          'type' => 'integer',
          'isa' => 'int',
          'label' => '產品',
          'renderAs' => 'SelectInput',
          'widgetAttributes' => array( 
            ),
          'refer' => 'ProductBundle\\Model\\ProductSchema',
        ),
    ),
  'name' => array( 
      'name' => 'name',
      'attributes' => array( 
          'type' => 'varchar(120)',
          'isa' => 'str',
          'size' => 120,
          'required' => true,
          'label' => '類型名稱',
          'renderAs' => 'TextInput',
          'widgetAttributes' => array( 
              'size' => 20,
              'placeholder' => '如: 綠色, 黑色, 羊毛, 大、中、小等等。',
            ),
        ),
    ),
  'quantity' => array( 
      'name' => 'quantity',
      'attributes' => array( 
          'type' => 'integer',
          'isa' => 'int',
          'default' => 0,
          'label' => '數量',
          'renderAs' => 'TextInput',
          'widgetAttributes' => array( 
            ),
          'validValues' => array( 
              -1,
              0,
              1,
              2,
              3,
              4,
              5,
              6,
              7,
              8,
              9,
              10,
              11,
              12,
              13,
              14,
              15,
              16,
              17,
              18,
              19,
              20,
              21,
              22,
              23,
              24,
              25,
              26,
              27,
              28,
              29,
              30,
              31,
              32,
              33,
              34,
              35,
              36,
              37,
              38,
              39,
              40,
              41,
              42,
              43,
              44,
              45,
              46,
              47,
              48,
              49,
              50,
              51,
              52,
              53,
              54,
              55,
              56,
              57,
              58,
              59,
              60,
              61,
              62,
              63,
              64,
              65,
              66,
              67,
              68,
              69,
              70,
              71,
              72,
              73,
              74,
              75,
              76,
              77,
              78,
              79,
              80,
              81,
              82,
              83,
              84,
              85,
              86,
              87,
              88,
              89,
              90,
              91,
              92,
              93,
              94,
              95,
              96,
              97,
              98,
              99,
              100,
            ),
        ),
    ),
  'comment' => array( 
      'name' => 'comment',
      'attributes' => array( 
          'type' => 'text',
          'isa' => 'str',
          'label' => '備註',
          'renderAs' => 'TextareaInput',
          'widgetAttributes' => array( 
            ),
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
  'product_id',
  'name',
  'quantity',
  'comment',
);
        $this->primaryKey      = 'id';
        $this->table           = 'product_types';
        $this->modelClass      = 'ProductBundle\\Model\\ProductType';
        $this->collectionClass = 'ProductBundle\\Model\\ProductTypeCollection';
        $this->label           = '產品類型';
        $this->relations       = array( 
  'product' => \LazyRecord\Schema\Relationship::__set_state(array( 
  'data' => array( 
      'type' => 4,
      'self_schema' => 'ProductBundle\\Model\\ProductTypeSchema',
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
        _('產品類型');
        _('產品');
        _('類型名稱');
        _('數量');
        _('備註');
    }

}