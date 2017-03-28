<?php

use suda\archive\DTOManager;
defined('DTA_TPL') or define('DTA_TPL', SYS_RES.'/tpl');

DTOManager::parserDto();
DTOManager::backup();