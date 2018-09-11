<?php

if (PHP_SAPI === 'cli') {
    require_once __DIR__ .'/suda-cli.php';
} else {
    require_once __DIR__ .'/suda.php';
}
