<?php
namespace cn\atd3;

class Install
{
    /**
     * This will be called when this module first run
     * change callback on module.json 
     * @return void
     */
    public static function task()
    {
        // ensure database
        (new \suda\core\Query('CREATE DATABASE IF NOT EXISTS `'.conf('database.name').'` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;'))->exec();
        // TODO install task
        debug()->info('run_install_task');
    }
}
