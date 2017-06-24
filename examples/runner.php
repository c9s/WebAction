<?php
require '../vendor/autoload.php';

$container = new DefaultConfigurations;
$runner = new ActionRunner($container);

if ($result = $runner->handleWith(STDOUT, $_REQUEST)) {
    var_dump( $result ); 
}
