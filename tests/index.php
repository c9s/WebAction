<?php
require 'bootstrap.php';

$config = new Maghead\ConfigLoader;
$config->load('../.lazy.yml');
$config->init();

WebAction\RecordAction\BaseRecordAction::createCRUDClass('Product\\Model\\ProductCategory', 'Create');
WebAction\RecordAction\BaseRecordAction::createCRUDClass('Product\\Model\\ProductCategory', 'Update');
WebAction\RecordAction\BaseRecordAction::createCRUDClass('Product\\Model\\Category', 'Create');
WebAction\RecordAction\BaseRecordAction::createCRUDClass('Product\\Model\\Category', 'Update');
WebAction\RecordAction\BaseRecordAction::createCRUDClass('Product\\Model\\ProductType', 'Create');
WebAction\RecordAction\BaseRecordAction::createCRUDClass('Product\\Model\\ProductType', 'Update');

// handle actions
if ( isset($_REQUEST['action']) ) {
    try {
        $container = new WebAction\ServiceContainer;
        $runner = new WebAction\ActionRunner($container);
        $result = $runner->run( $_REQUEST['action'] );
        if ( $result && $runner->isAjax() ) {
            // Deprecated:
            // The text/plain seems work for IE8 (IE8 wraps the 
            // content with a '<pre>' tag.
            header('Cache-Control: no-cache');
            header('Content-Type: text/plain; Charset=utf-8');

            // Since we are using "textContent" instead of "innerHTML" attributes
            // we should output the correct json mime type.
            // header('Content-Type: application/json; Charset=utf-8');
            echo $result->__toString();
            exit(0);
        }
    } catch ( Exception $e ) {
        /**
            * Return 403 status forbidden
            */
        header('HTTP/1.0 403');
        if ( $runner->isAjax() ) {
            die(json_encode(array(
                    'error' => 1,
                    'message' => $e->getMessage()
            )));
        } else {
            die( $e->getMessage() );
        }
    }
}


if ( isset($result) ) {
    var_dump($result->message);
}


$class = WebAction\RecordAction\BaseRecordAction::createCRUDClass('Product\\Model\\Product', 'Create');
$create = new $class;
echo $create->asView()->render();
