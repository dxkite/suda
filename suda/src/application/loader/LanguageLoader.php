<?php
namespace suda\application\loader;

use suda\framework\Config;
use suda\application\Application;
use suda\application\LanguageBag;

/**
 * 应用程序
 */
class LanguageLoader
{
    public static function load(Application $application)
    {
        $language = $application->getLocate();
        $languageBag = new LanguageBag;
        $path = $application->getResource()->getConfigResourcePath('locale/'.$language);
        $languageBag = static::loadFrom($languageBag, $path);
        if ($module = $application->getRunning()) {
            $path = $module->getResource()->getConfigResourcePath('locale/'.$language);
            $languageBag = static::loadFrom($languageBag, $path);
        }
        $application->setLanguage($languageBag);
    }

    protected static function loadFrom(LanguageBag $languageBag, ?string $path)
    {
        if ($path !== null) {
            $lang = Config::loadConfig($path) ?? [];
            $languageBag->assign($lang);
        }
        return $languageBag;
    }
}
