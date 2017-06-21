<?php
namespace WebAction\Model\CRUDTest;

class FooUserCollectionBase  extends \Maghead\Runtime\Collection {
const schema_proxy_class = '\\WebAction\\Model\\CRUDTest\\FooUserSchemaProxy';
const model_class = '\\WebAction\\Model\\CRUDTest\\FooUser';
const table = 'foo_users';

}
