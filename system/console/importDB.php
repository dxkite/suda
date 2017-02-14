<?php

require_once __DIR__ .'/../suda-console.php';

if (isset($argv[1])) {
    Database::import($argv[1]);
} else {
    Database::import(RESOURCE_DIR.'/install.php');
}
