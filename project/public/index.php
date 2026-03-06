<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Config\Database;
use App\Models\Classes\Joueur;
ini_set('display_errors', 1);
error_reporting(E_ALL);
define('BASE_URL', '/capaci/project/public');

session_start();

$router = new Router();

$router->handleRequest();
