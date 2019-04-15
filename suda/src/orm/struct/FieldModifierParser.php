<?php
namespace suda\orm\struct;

/**
 * 字段修饰符解析
 */
class FieldModifierParser
{
    /**
     * 判断位置
     *
     * @var int
     */
    protected $pos;

    /**
     * 长度
     *
     * @var int
     */
    protected $length;


    /**
     * Tokens
     *
     * @var array
     */
    protected $tokens;

    public function isNotEnd()
    {
        return $this->pos < $this->length;
    }

    public function parse(string $modifier)
    {
        $this->tokens = \token_get_all('<?php '. $modifier);
        $this->length = count($this->tokens);
        $this->pos = 1;
        $modifier = [];
        while ($this->isNotEnd()) {
            $token = $this->tokens[$this->pos];
            if (\is_array($token)) {
                if ($token[0] === T_STRING || $token[0] === T_DEFAULT) {
                    $name = $token[1];
                    $paramter = $this->getParameter($this->tokens);
                    $modifier[]=[$name, $paramter];
                }
            }
            $this->pos++;
        }
        return $modifier;
    }

    public function getParameter()
    {
        $this->skipWhiteComment();
        $paramter = [];
        if ($this->skipAfterLeftBorder()) {
            $token = $this->tokens[$this->pos];
            $paramter[] = $this->getValue($token);
            $this->pos++;
            while ($this->nextIsNotEnd() === true) {
                $token = $this->tokens[$this->pos];
                $paramter[] = $this->getValue($token);
                $this->pos++;
            }
        }
        return $paramter;
    }

    public function skipWhiteComment()
    {
        for ($i = $this->pos + 1; $i < $this->length ; $i++) {
            if (is_array($this->tokens[$i])) {
                if (in_array($this->tokens[$i][0], [T_COMMENT, T_DOC_COMMENT, T_WHITESPACE])) {
                    $this->pos++;
                }
            }
            return;
        }
    }

    public function skipAfterLeftBorder()
    {
        $this->skipWhiteComment();
        if ($this->pos + 1 < $this->length && $this->tokens[$this->pos + 1][0] === '(') {
            $this->pos += 2;
            if ($this->pos < $this->length && $this->tokens[$this->pos] !== ')') {
                return true;
            } else {
                $this->pos++;
            }
        }
        return false;
    }


    public function getValue($token)
    {
        if ($token[0] === T_STRING) {
            return $this->getConstValue($token[1]);
        } elseif ($token[0] === T_CONSTANT_ENCAPSED_STRING) {
            return $this->getStringValue($token[1]);
        } elseif ($token[0] === T_LNUMBER) {
            return $this->getNumberValue($token[1]);
        }
        return null;
    }

    public function getConstValue(string $value)
    {
        $value = strtolower($value);
        if ($value === 'true') {
            return true;
        }
        if ($value === 'false') {
            return false;
        }
        return null;
    }

    public function getStringValue(string $value)
    {
        $value = trim($value, '\'"');
        return stripslashes($value);
    }


    public function getNumberValue(string $value)
    {
        return intval($value);
    }

    public function nextIsNotEnd()
    {
        $this->skipWhiteComment();

        $token = $this->tokens[$this->pos];
        if (is_array($token)) {
            return false;
        }
        if ($token === ')') {
            $this->pos++;
            $this->skipWhiteComment();
            return false;
        }
        if ($token === ',') {
            $this->pos++;
            $this->skipWhiteComment();
            return true;
        }
    }
}
