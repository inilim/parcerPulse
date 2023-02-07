<?php
error_reporting(E_ALL);
date_default_timezone_set('Etc/GMT-3');

if (ob_get_level()) ob_end_clean();

require __DIR__ . '/vendor/autoload.php';

use \Bramus\Router\Router;

$router = new Router;


# Main
$router->get('/', function()
{
   # контроллер
});


$router->run();
