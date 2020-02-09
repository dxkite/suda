<?php
/**
 * 控制台用
 */
defined('SUDA_APP') or define('SUDA_APP', __DIR__ . '/app');
defined('SUDA_DATA') or define('SUDA_DATA', __DIR__ . '/data');
defined('SUDA_PUBLIC') or define('SUDA_PUBLIC', __DIR__ . '/public');

defined('SUDA_DEBUG') or define('SUDA_DEBUG', true);
defined('SUDA_DEBUG_LEVEL') or define('SUDA_DEBUG_LEVEL', 'debug');
defined('SUDA_APP_MANIFEST') or define('SUDA_APP_MANIFEST', SUDA_APP . '/manifest');
defined('SUDA_DEBUG_LOG_PATH') or define('SUDA_DEBUG_LOG_PATH', SUDA_DATA . '/logs');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/suda/loader/console.php';