<?php
namespace suda\orm\struct;

use function call_user_func_array;
use function is_array;
use function method_exists;
use suda\orm\struct\Field;
use function token_get_all;

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

    /**
     * 修饰符
     *
     * @var array
     */
    protected $modifier;
    
    /**
     * 解析
     *
     * @param string $modifier
     * @return $this
     */
    public function parse(string $modifier)
    {
        $this->tokens = token_get_all('<?php '. $modifier);
        $this->length = count($this->tokens);
        $this->pos = 1;
        $this->modifier = [];
        while ($this->isNotEnd()) {
            $token = $this->tokens[$this->pos];
            if (is_array($token)) {
                if ($token[0] === T_STRING || $token[0] === T_DEFAULT) {
                    $name = $token[1];
                    $parameter = $this->getParameter();
                    $this->modifier[] = [$name, $parameter];
                }
            }
            $this->pos++;
        }
        return $this;
    }

    /**
     * 修改
     *
     * @param \suda\orm\struct\Field $field
     * @return void
     */
    public function modify(Field $field)
    {
        foreach ($this->modifier as $value) {
            list($name, $parameter) = $value;
            if (method_exists($field, $name)) {
                call_user_func_array([$field, $name], $parameter);
            }
        }
    }

    public function getModifier()
    {
        return $this->modifier;
    }

    protected function isNotEnd()
    {
        return $this->pos < $this->length;
    }
    
    protected function getParameter()
    {
        $this->skipWhiteComment();
        $paramter = [];
        if ($this->skipAfterLeftBorder()) {
            do {
                $token = $this->tokens[$this->pos];
                $paramter[] = $this->getValue($token);
                $this->pos++;
            } while ($this->nextIsNotEnd() === true);
        }
        return $paramter;
    }

    protected function skipWhiteComment()
    {
        for ($i = $this->pos + 1; $i < $this->length ; $i++) {
            if (is_array($this->tokens[$i])) {
                if (in_array($this->tokens[$i][0], [T_COMMENT, T_DOC_COMMENT, T_WHITESPACE])) {
                    $this->pos++;
                }
            } else {
                return;
            }
        }
    }

    protected function skipAfterLeftBorder()
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


    protected function getValue($token)
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

    protected function getConstValue(string $value)
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

    protected function getStringValue(string $value)
    {
        $value = trim($value, '\'"');
        return stripslashes($value);
    }


    protected function getNumberValue(string $value)
    {
        return intval($value);
    }

    protected function nextIsNotEnd()
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
