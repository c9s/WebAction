<?php
namespace WebAction\Model\CRUDTest;

class FooUser extends \Maghead\Runtime\Model {
    public function schema($schema) 
    {
        $schema->column('username')->varchar(12);
        $schema->column('password')->varchar(12);
    }
#boundary start 2d278467a6071e8ac2130d201b3510e1
	const schema_proxy_class = 'WebAction\\Model\\CRUDTest\\FooUserSchemaProxy';
	const collection_class = 'WebAction\\Model\\CRUDTest\\FooUserCollection';
	const model_class = 'WebAction\\Model\\CRUDTest\\FooUser';
	const table = 'foo_users';
#boundary end 2d278467a6071e8ac2130d201b3510e1
}

