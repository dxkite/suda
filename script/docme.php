<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 * 
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.13
 */

require_once __DIR__ .'/../system/suda-console.php';

\suda\core\Autoloader::addIncludePath(__DIR__.'/docme/src','docme');


$summary=new docme\Docme;

$summary->include(SYSTEM_DIR.'/src');
$summary->setFunctions(docme\FunctionExport::getUserDefinedFunctions());
$summary->setClasses(docme\ClassExport::getUserDefinedClasses());
$summary->export(__DIR__.'/../docs');
