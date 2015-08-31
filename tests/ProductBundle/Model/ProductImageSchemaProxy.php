<?php
/**
 * THIS FILE IS AUTO-GENERATED BY LAZYRECORD,
 * PLEASE DO NOT MODIFY THIS FILE DIRECTLY.
 * 
 * Last Modified: August 31st at 8:41pm
 */
namespace ProductBundle\Model;


use LazyRecord;
use LazyRecord\Schema\RuntimeSchema;
use LazyRecord\Schema\Relationship;

class ProductImageSchemaProxy extends RuntimeSchema
{

    public static $column_names = array (
  0 => 'product_id',
  1 => 'title',
  2 => 'image',
  3 => 'large',
  4 => 'id',
);
    public static $column_hash = array (
  'product_id' => 1,
  'title' => 1,
  'image' => 1,
  'large' => 1,
  'id' => 1,
);
    public static $mixin_classes = array (
);
    public static $column_names_include_virtual = array (
  0 => 'product_id',
  1 => 'title',
  2 => 'image',
  3 => 'large',
  4 => 'id',
);

    const schema_class = 'ProductBundle\\Model\\ProductImageSchema';
    const collection_class = 'ProductBundle\\Model\\ProductImageCollection';
    const model_class = 'ProductBundle\\Model\\ProductImage';
    const model_name = 'ProductImage';
    const model_namespace = 'ProductBundle\\Model';
    const primary_key = 'id';
    const table = 'product_images';
    const label = '產品圖';

    public function __construct()
    {
        /** columns might have closure, so it can not be const */
        $this->columnData      = array( 
  'product_id' => array( 
      'name' => 'product_id',
      'attributes' => array( 
          'locales' => NULL,
          'attributes' => array( 
              'refer' => 'ProductBundle\\Model\\Product',
              'renderAs' => 'SelectInput',
              'widgetAttributes' => array( 
                ),
              'label' => '產品',
            ),
          'name' => 'product_id',
          'primary' => NULL,
          'unsigned' => NULL,
          'type' => 'int',
          'isa' => 'int',
          'notNull' => NULL,
          'enum' => NULL,
          'set' => NULL,
          'attributeTypes' => array( 
              'primary' => 6,
              'size' => 3,
              'autoIncrement' => 6,
              'immutable' => 6,
              'unique' => 6,
              'null' => 6,
              'notNull' => 6,
              'typeConstraint' => 6,
              'timezone' => 6,
              'renderable' => 6,
              'label' => 0,
              'desc' => 2,
              'comment' => 2,
              'refer' => 2,
              'default' => 0,
              'validator' => 0,
              'validatorArgs' => 0,
              'validValues' => 0,
              'validValueBuilder' => 5,
              'optionValues' => 0,
              'validPairs' => 0,
              'canonicalizer' => 5,
              'virtual' => 6,
              'filter' => 5,
              'inflator' => 5,
              'deflator' => 5,
              'renderAs' => 2,
              'widgetAttributes' => 1,
              'contentType' => 2,
              'primaryField' => 6,
              'length' => 3,
              'decimals' => 3,
            ),
          'refer' => 'ProductBundle\\Model\\Product',
          'renderAs' => 'SelectInput',
          'widgetAttributes' => array( 
            ),
          'label' => '產品',
        ),
    ),
  'title' => array( 
      'name' => 'title',
      'attributes' => array( 
          'locales' => NULL,
          'attributes' => array( 
              'length' => 130,
              'label' => '圖片標題',
            ),
          'name' => 'title',
          'primary' => NULL,
          'unsigned' => NULL,
          'type' => 'varchar',
          'isa' => 'str',
          'notNull' => NULL,
          'enum' => NULL,
          'set' => NULL,
          'attributeTypes' => array( 
              'primary' => 6,
              'size' => 3,
              'autoIncrement' => 6,
              'immutable' => 6,
              'unique' => 6,
              'null' => 6,
              'notNull' => 6,
              'typeConstraint' => 6,
              'timezone' => 6,
              'renderable' => 6,
              'label' => 0,
              'desc' => 2,
              'comment' => 2,
              'refer' => 2,
              'default' => 0,
              'validator' => 0,
              'validatorArgs' => 0,
              'validValues' => 0,
              'validValueBuilder' => 5,
              'optionValues' => 0,
              'validPairs' => 0,
              'canonicalizer' => 5,
              'virtual' => 6,
              'filter' => 5,
              'inflator' => 5,
              'deflator' => 5,
              'renderAs' => 2,
              'widgetAttributes' => 1,
              'contentType' => 2,
              'primaryField' => 6,
              'length' => 3,
              'decimals' => 3,
            ),
          'length' => 130,
          'label' => '圖片標題',
        ),
    ),
  'image' => array( 
      'name' => 'image',
      'attributes' => array( 
          'locales' => NULL,
          'attributes' => array( 
              'length' => 130,
              'label' => '圖',
            ),
          'name' => 'image',
          'primary' => NULL,
          'unsigned' => NULL,
          'type' => 'varchar',
          'isa' => 'str',
          'notNull' => true,
          'enum' => NULL,
          'set' => NULL,
          'attributeTypes' => array( 
              'primary' => 6,
              'size' => 3,
              'autoIncrement' => 6,
              'immutable' => 6,
              'unique' => 6,
              'null' => 6,
              'notNull' => 6,
              'typeConstraint' => 6,
              'timezone' => 6,
              'renderable' => 6,
              'label' => 0,
              'desc' => 2,
              'comment' => 2,
              'refer' => 2,
              'default' => 0,
              'validator' => 0,
              'validatorArgs' => 0,
              'validValues' => 0,
              'validValueBuilder' => 5,
              'optionValues' => 0,
              'validPairs' => 0,
              'canonicalizer' => 5,
              'virtual' => 6,
              'filter' => 5,
              'inflator' => 5,
              'deflator' => 5,
              'renderAs' => 2,
              'widgetAttributes' => 1,
              'contentType' => 2,
              'primaryField' => 6,
              'length' => 3,
              'decimals' => 3,
            ),
          'length' => 130,
          'label' => '圖',
        ),
    ),
  'large' => array( 
      'name' => 'large',
      'attributes' => array( 
          'locales' => NULL,
          'attributes' => array( 
              'length' => 130,
              'label' => '最大圖',
            ),
          'name' => 'large',
          'primary' => NULL,
          'unsigned' => NULL,
          'type' => 'varchar',
          'isa' => 'str',
          'notNull' => NULL,
          'enum' => NULL,
          'set' => NULL,
          'attributeTypes' => array( 
              'primary' => 6,
              'size' => 3,
              'autoIncrement' => 6,
              'immutable' => 6,
              'unique' => 6,
              'null' => 6,
              'notNull' => 6,
              'typeConstraint' => 6,
              'timezone' => 6,
              'renderable' => 6,
              'label' => 0,
              'desc' => 2,
              'comment' => 2,
              'refer' => 2,
              'default' => 0,
              'validator' => 0,
              'validatorArgs' => 0,
              'validValues' => 0,
              'validValueBuilder' => 5,
              'optionValues' => 0,
              'validPairs' => 0,
              'canonicalizer' => 5,
              'virtual' => 6,
              'filter' => 5,
              'inflator' => 5,
              'deflator' => 5,
              'renderAs' => 2,
              'widgetAttributes' => 1,
              'contentType' => 2,
              'primaryField' => 6,
              'length' => 3,
              'decimals' => 3,
            ),
          'length' => 130,
          'label' => '最大圖',
        ),
    ),
  'id' => array( 
      'name' => 'id',
      'attributes' => array( 
          'locales' => NULL,
          'attributes' => array( 
              'autoIncrement' => true,
            ),
          'name' => 'id',
          'primary' => true,
          'unsigned' => NULL,
          'type' => 'int',
          'isa' => 'int',
          'notNull' => true,
          'enum' => NULL,
          'set' => NULL,
          'attributeTypes' => array( 
              'primary' => 6,
              'size' => 3,
              'autoIncrement' => 6,
              'immutable' => 6,
              'unique' => 6,
              'null' => 6,
              'notNull' => 6,
              'typeConstraint' => 6,
              'timezone' => 6,
              'renderable' => 6,
              'label' => 0,
              'desc' => 2,
              'comment' => 2,
              'refer' => 2,
              'default' => 0,
              'validator' => 0,
              'validatorArgs' => 0,
              'validValues' => 0,
              'validValueBuilder' => 5,
              'optionValues' => 0,
              'validPairs' => 0,
              'canonicalizer' => 5,
              'virtual' => 6,
              'filter' => 5,
              'inflator' => 5,
              'deflator' => 5,
              'renderAs' => 2,
              'widgetAttributes' => 1,
              'contentType' => 2,
              'primaryField' => 6,
              'length' => 3,
              'decimals' => 3,
            ),
          'autoIncrement' => true,
        ),
    ),
);
        $this->columnNames     = array( 
  'id',
  'product_id',
  'title',
  'image',
  'large',
);
        $this->primaryKey      = 'id';
        $this->table           = 'product_images';
        $this->modelClass      = 'ProductBundle\\Model\\ProductImage';
        $this->collectionClass = 'ProductBundle\\Model\\ProductImageCollection';
        $this->label           = '產品圖';
        $this->relations       = array( 
  'product' => \LazyRecord\Schema\Relationship::__set_state(array( 
  'data' => array( 
      'type' => 3,
      'self_schema' => 'ProductBundle\\Model\\ProductImageSchema',
      'self_column' => 'product_id',
      'foreign_schema' => 'ProductBundle\\Model\\ProductSchema',
      'foreign_column' => 'id',
    ),
  'accessor' => 'product',
  'where' => NULL,
  'orderBy' => array( 
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
        _('產品圖');
        _('產品');
        _('圖片標題');
        _('圖');
        _('最大圖');
    }

}
