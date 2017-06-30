<?php

namespace JsPhpize;

use JsPhpize\Lexer\Pattern;

class JsPhpizeOptions
{
    /**
     * Prefix for specific constants.
     *
     * @const string
     */
    const CONST_PREFIX = '__JPC_';

    /**
     * Prefix for specific variables.
     *
     * @const string
     */
    const VAR_PREFIX = '__jpv_';

    /**
     * Pass options as array or no parameters for all options on default value.
     *
     * @param array $options list of options.
     */
    public function __construct(array $options = array())
    {
        $this->options = $options;
        if (!isset($this->options['patterns'])) {
            $this->options['patterns'] = array(
                new Pattern(10, 'newline', '\n'),
                new Pattern(20, 'comment', '\/\/.*?\n|\/\*[\s\S]*?\*\/'),
                new Pattern(30, 'string', '"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\''),
                new Pattern(35, 'regexp', '\\/(?:\\\\.|[^\\/\\\\])*\\/[gimuy]*'),
                new Pattern(40, 'number', '0[bB][01]+|0[oO][0-7]+|0[xX][0-9a-fA-F]+|(\d+(\.\d*)?|\.\d+)([eE]-?\d+)?'),
                new Pattern(50, 'lambda', '=>'),
                new Pattern(60, 'operator', array('delete', 'typeof', 'void'), true),
                new Pattern(70, 'operator', array('>>>=', '<<=', '>>=', '**=')),
                new Pattern(80, 'operator', array('++', '--', '&&', '||', '**', '>>>', '<<', '>>')),
                new Pattern(90, 'operator', array('===', '!==', '>=', '<=', '<>', '!=', '==', '>', '<')),
                new Pattern(100, 'operator', '[\\|\\^&%\\/\\*\\+\\-]='),
                new Pattern(110, 'operator', '[\\[\\]\\{\\}\\(\\)\\:\\.\\/\\*~\\!\\^\\|&%\\?,;\\+\\-]'),
                new Pattern(120, 'keyword', array('as', 'async', 'await', 'break', 'case', 'catch', 'class', 'const', 'continue', 'debugger', 'default', 'do', 'else', 'enum', 'export', 'extends', 'finally', 'for', 'from', 'function', 'get', 'if', 'implements', 'import', 'in', 'instanceof', 'interface', 'let', 'new', 'of', 'package', 'private', 'protected', 'public', 'return', 'set', 'static', 'super', 'switch', 'throw', 'try', 'var', 'while', 'with', 'yield', 'yield*'), true),
                new Pattern(130, 'constant', 'null|undefined|Infinity|NaN|true|false|Math\.[A-Z][A-Z0-9_]*|[A-Z][A-Z0-9\\\\_\\x7f-\\xff]*|[\\\\\\x7f-\\xff_][A-Z0-9\\\\_\\x7f-\\xff]*[A-Z][A-Z0-9\\\\_\\x7f-\\xff]*', true),
                new Pattern(130, 'variable', '[a-zA-Z\\\\\\x7f-\\xff\\$_][a-zA-Z0-9\\\\_\\x7f-\\xff\\$]*', '$'),
                new Pattern(140, 'operator', '[\\s\\S]'),
            );
        }
    }

    /**
     * Add a pattern.
     *
     * @param Pattern $pattern
     *
     * @return $this
     */
    public function addPattern(Pattern $pattern)
    {
        $this->options['patterns'][] = $pattern;

        return $this;
    }

    /**
     * Remove patterns using a filter function.
     *
     * @param callable $removeFunction
     *
     * @return $this
     */
    public function removePatterns($removeFunction)
    {
        $this->options['patterns'] = array_filter($this->options['patterns'], $removeFunction);

        return $this;
    }

    /**
     * Retrieve an option value.
     *
     * @param string $key     option name.
     * @param mixed  $default value to return if the option is not set.
     *
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }

    /**
     * Retrieve the prefix of specific variables.
     *
     * @return string
     */
    public function getVarPrefix()
    {
        return $this->getOption('varPrefix', static::VAR_PREFIX);
    }

    /**
     * Retrieve the prefix of specific variables.
     *
     * @return string
     */
    public function getHelperName($key)
    {
        $helpers = $this->getOption('helpers', array());

        return is_array($helpers) && isset($helpers[$key])
            ? $helpers[$key]
            : $key;
    }

    /**
     * Retrieve the prefix of specific constants.
     *
     * @return string
     */
    public function getConstPrefix()
    {
        return $this->getOption('constPrefix', static::CONST_PREFIX);
    }
}
