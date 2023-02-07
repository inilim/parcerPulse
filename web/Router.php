<?php
error_reporting(E_ALL);
date_default_timezone_set('Etc/GMT-3');

if (ob_get_level()) ob_end_clean();

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/_functions.php';
require __DIR__ . '/Render.php';
require __DIR__ . '/MainPage.php';

use \Bramus\Router\Router;

$router = new Router;

# Main
$router->get('/', function()
{
   new MainPage();
});

$router->get('/page\-([1-9][0-9]{0,})',
function ($page)
{
   new MainPage($page);
});


$router->run();
