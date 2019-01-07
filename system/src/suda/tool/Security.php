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
        'script-src' => ['self','unsafe-inline','unsafe-eval', 'nonce'],
        'style-src' => ['self','unsafe-inline','nonce'],
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
    protected static $keywords = ['self','unsafe-inline','unsafe-eval','unsafe-hashes','strict-dynamic','report-sample','unsafe-allow-redirects'];
    /**
     * 安全Nonce
     *
     * @var string|null
     */
    protected static $nonce = null;

    public static function getDefaultCsp():array
    {
        return static::$defaultCsp;
    }

    public static function getNonce():string
    {
        if (is_null(self::$nonce)) {
            self::$nonce = base64_encode(md5(\microtime(true), true));
        }
        return self::$nonce;
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
                    if ($rule = self::getRule($ruleNameChild, $nonce)) {
                        $scpRules .= $rule.' ';
                    }
                }
            } elseif ($rule = self::getRule($ruleNameChild, $nonce)) {
                $scpRules .= $rule.' ';
            }
            $scpRules = trim($scpRules) .';';
        }
        return \rtrim($scpRules, ';');
    }

    protected static function getRule(string $ruleName, ?string $nonce):?string
    {
        $lowerRuleName = strtolower($ruleName);
        $scpRules = null;
        if (\in_array($lowerRuleName, static::$keywords) || \preg_match('/^sha(256|384|512)\-/', $lowerRuleName) === 1) {
            $scpRules = "'{$lowerRuleName}'";
        } elseif ($lowerRuleName === 'nonce') {
            if ($nonce) {
                $scpRules = "'nonce-{$nonce}'";
            }
        } else {
            $scpRules = $ruleName;
        }
        return $scpRules;
    }
}
