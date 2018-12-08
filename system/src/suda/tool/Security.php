<?php
namespace suda\tool;

/**
 * 安全辅助工具
 */
class Security
{
    protected static $defaultCsp = [
        'default-src' => ['self'],
        'img-src' => ['self','data:'],
        'script-src' => ['self','nonce','unsafe-eval'],
        'style-src' => ['self','nonce','unsafe-inline'],
    ];

    /**
     * CSP的关键字
     * 
     * @link https://www.w3.org/TR/CSP3
     * 
     * keyword-source = "'self'" / "'unsafe-inline'" / "'unsafe-eval'"
     *           / "'strict-dynamic'" / "'unsafe-hashes'" /
     *           / "'report-sample'" / "'unsafe-allow-redirects'"
     */
    protected static $keywords = ['self','unsafe-eval','unsafe-eval','unsafe-hashes','strict-dynamic','report-sample','unsafe-allow-redirects'];

    public static function getDefaultCsp():array {
        return static::$defaultCsp;
    }

    public static function cspGeneretor($scpConfig, ?string $nonce):?string
    {
        if (\is_string($scpConfig)) {
            return $scpConfig;
        }
        if (is_null($scpConfig)) {
            $scpConfig = self::$defaultCsp;
        }
        
        $scpRules = '';
        foreach ($scpConfig as $ruleName => $ruleValue) {
            $scpRules .= $ruleName.' ';
            if (\is_array($ruleValue)) {
                foreach ($ruleValue as $ruleNameChild) {
                    $scpRules .= self::getRule($ruleNameChild, $nonce).' ';
                }
            } else {
                $scpRules .= self::getRule($ruleValue, $nonce).' ';
            }
            $scpRules = trim($scpRules) .';';
        }
        return \rtrim($scpRules, ';');
    }

    protected static function getRule(string $ruleName, ?string $nonce)
    {
        $lowerRuleName = strtolower($ruleName);
        if (\in_array($lowerRuleName, static::$keywords)) {
            $scpRules = "'{$lowerRuleName}'";
        } elseif ($lowerRuleName === 'nonce' || $nonce) {
            if ($nonce) {
                $scpRules = "'nonce-{$nonce}'";
            }
        } else {
            $scpRules = $ruleName;
        }
        return $scpRules;
    }
}
