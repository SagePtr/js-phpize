<?php

namespace JsPhpize\Lexer;

use JsPhpize\JsPhpize;

class Lexer extends Scanner
{
    /**
     * @var string
     */
    protected $input;

    /**
     * @var JsPhpize
     */
    protected $engine;

    /**
     * @var int
     */
    protected $line;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $fileInfo = null;

    /**
     * @var string
     */
    protected $consumed = '';

    public function __construct(JsPhpize $engine, $input, $filename)
    {
        $this->engine = $engine;
        $this->filename = $filename;
        $this->line = 1;
        $disallow = $engine->getOption('disallow', array());
        if (is_string($disallow)) {
            $disallow = explode(' ', $disallow);
        }
        $this->disallow = array_map('strtolower', (array) $disallow);
        $this->input = trim($input);
    }

    public function exceptionInfos()
    {
        if (is_null($this->fileInfo)) {
            $this->fileInfo = $this->filename ? ' in ' . realpath($this->filename) : '';
        }

        return
            $this->fileInfo .
            ' on line ' . $this->line .
            ' near from ' . trim($this->consumed);
    }

    protected function consume($consumed)
    {
        $consumed = is_int($consumed) ? substr($this->input, 0, $consumed) : $consumed;
        $this->consumed = strlen(trim($consumed)) > 1 ? $consumed : $this->consumed . $consumed;
        $this->line += substr_count($consumed, "\n");
        $this->input = substr($this->input, strlen($consumed));
    }

    protected function token($type, $data = array())
    {
        $className = $this->engine->getOption('tokenClass', '\\JsPhpize\\Lexer\\Token');

        return new $className($type, is_string($data) ? array('value' => $data) : (array) $data);
    }

    protected function typeToken($matches)
    {
        $this->consume($matches[0]);

        return $this->token(trim($matches[0]));
    }

    protected function valueToken($type, $matches)
    {
        $this->consume($matches[0]);

        return $this->token($type, trim($matches[0]));
    }

    protected function scan($pattern, $method)
    {
        if (preg_match('/^\s*(' . $pattern . ')/', $this->input, $matches)) {
            return $this->{'scan' . ucfirst($method)}($matches);
        }
    }

    /**
     * @return Token|false
     *
     * @throws Exception
     */
    public function next()
    {
        if (!strlen($this->input)) {
            return false;
        }

        $patterns = $this->engine->getOption('patterns');
        usort($patterns, function (Pattern $a, Pattern $b) {
            return $a->priority - $b->priority;
        });

        foreach ($patterns as $pattern) {
            if ($token = $this->scan($pattern->regex, $pattern->type)) {
                if (in_array($pattern->type, $this->disallow)) {
                    throw new Exception($pattern->type . ' is disallowed.', 3);
                }

                return $token;
            }
        }

        throw new Exception('Unknow pattern found at: ' . substr($this->input, 0, 100), 12);
    }
}
