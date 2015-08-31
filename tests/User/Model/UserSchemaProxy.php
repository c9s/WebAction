<?php
/**
 * THIS FILE IS AUTO-GENERATED BY LAZYRECORD,
 * PLEASE DO NOT MODIFY THIS FILE DIRECTLY.
 * 
 * Last Modified: August 31st at 8:41pm
 */
namespace User\Model;


use LazyRecord;
use LazyRecord\Schema\RuntimeSchema;
use LazyRecord\Schema\Relationship;

class UserSchemaProxy extends RuntimeSchema
{

    public static $column_names = array (
  0 => 'name',
  1 => 'email',
  2 => 'password',
  3 => 'id',
);
    public static $column_hash = array (
  'name' => 1,
  'email' => 1,
  'password' => 1,
  'id' => 1,
);
    public static $mixin_classes = array (
);
    public static $column_names_include_virtual = array (
  0 => 'name',
  1 => 'email',
  2 => 'password',
  3 => 'id',
);

    const schema_class = 'User\\Model\\UserSchema';
    const collection_class = 'User\\Model\\UserCollection';
    const model_class = 'User\\Model\\User';
    const model_name = 'User';
    const model_namespace = 'User\\Model';
    const primary_key = 'id';
    const table = 'users';
    const label = 'User';

    public function __construct()
    {
        /** columns might have closure, so it can not be const */
        $this->columnData      = array( 
  'name' => array( 
      'name' => 'name',
      'attributes' => array( 
          'locales' => NULL,
          'attributes' => array( 
              'length' => 30,
            ),
          'name' => 'name',
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
          'length' => 30,
        ),
    ),
  'email' => array( 
      'name' => 'email',
      'attributes' => array( 
          'locales' => NULL,
          'attributes' => array( 
              'length' => 128,
            ),
          'name' => 'email',
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
          'length' => 128,
        ),
    ),
  'password' => array( 
      'name' => 'password',
      'attributes' => array( 
          'locales' => NULL,
          'attributes' => array( 
              'length' => 128,
            ),
          'name' => 'password',
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
          'length' => 128,
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
  'name',
  'email',
  'password',
);
        $this->primaryKey      = 'id';
        $this->table           = 'users';
        $this->modelClass      = 'User\\Model\\User';
        $this->collectionClass = 'User\\Model\\UserCollection';
        $this->label           = 'User';
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
        _('User');
    }

}
