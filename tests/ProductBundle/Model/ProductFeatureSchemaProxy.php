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

class ProductFeatureSchemaProxy extends RuntimeSchema
{

    public static $column_names = array (
  0 => 'product_id',
  1 => 'feature_id',
  2 => 'id',
);
    public static $column_hash = array (
  'product_id' => 1,
  'feature_id' => 1,
  'id' => 1,
);
    public static $mixin_classes = array (
);
    public static $column_names_include_virtual = array (
  0 => 'product_id',
  1 => 'feature_id',
  2 => 'id',
);

    const schema_class = 'ProductBundle\\Model\\ProductFeatureSchema';
    const collection_class = 'ProductBundle\\Model\\ProductFeatureCollection';
    const model_class = 'ProductBundle\\Model\\ProductFeature';
    const model_name = 'ProductFeature';
    const model_namespace = 'ProductBundle\\Model';
    const primary_key = 'id';
    const table = 'product_feature_junction';
    const label = 'ProductFeature';

    public function __construct()
    {
        /** columns might have closure, so it can not be const */
        $this->columnData      = array( 
  'product_id' => array( 
      'name' => 'product_id',
      'attributes' => array( 
          'locales' => NULL,
          'attributes' => array( 
              'label' => 'Product Id',
              'refer' => 'ProductBundle\\Model\\Product',
            ),
          'name' => 'product_id',
          'primary' => NULL,
          'unsigned' => NULL,
          'type' => NULL,
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
          'label' => 'Product Id',
          'refer' => 'ProductBundle\\Model\\Product',
        ),
    ),
  'feature_id' => array( 
      'name' => 'feature_id',
      'attributes' => array( 
          'locales' => NULL,
          'attributes' => array( 
              'label' => 'Feature Id',
              'refer' => 'ProductBundle\\Model\\Feature',
            ),
          'name' => 'feature_id',
          'primary' => NULL,
          'unsigned' => NULL,
          'type' => NULL,
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
          'label' => 'Feature Id',
          'refer' => 'ProductBundle\\Model\\Feature',
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
  'feature_id',
);
        $this->primaryKey      = 'id';
        $this->table           = 'product_feature_junction';
        $this->modelClass      = 'ProductBundle\\Model\\ProductFeature';
        $this->collectionClass = 'ProductBundle\\Model\\ProductFeatureCollection';
        $this->label           = 'ProductFeature';
        $this->relations       = array( 
  'product' => \LazyRecord\Schema\Relationship::__set_state(array( 
  'data' => array( 
      'type' => 3,
      'self_schema' => 'ProductBundle\\Model\\ProductFeatureSchema',
      'self_column' => 'product_id',
      'foreign_schema' => 'ProductBundle\\Model\\ProductSchema',
      'foreign_column' => 'id',
    ),
  'accessor' => 'product',
  'where' => NULL,
  'orderBy' => array( 
    ),
)),
  'feature' => \LazyRecord\Schema\Relationship::__set_state(array( 
  'data' => array( 
      'type' => 3,
      'self_schema' => 'ProductBundle\\Model\\ProductFeatureSchema',
      'self_column' => 'feature_id',
      'foreign_schema' => 'ProductBundle\\Model\\FeatureSchema',
      'foreign_column' => 'id',
    ),
  'accessor' => 'feature',
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
        _('ProductFeature');
        _('Product Id');
        _('Feature Id');
    }

}
