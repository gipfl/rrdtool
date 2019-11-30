<?php

namespace gipfl\RrdTool\Parser;

use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;

class StreamingDefinitionParser implements EventEmitterInterface
{
    use EventEmitterTrait;

    protected $position;

    protected $string;

    protected $length;

    public function parse(& $string)
    {
        $this->string = & $string;
        $this->position = 0;
        $this->length = \strlen($string);
        $this->run();
    }

    protected function run()
    {
        while ($this->position < $this->length) {
            $this->skipWhitespace();
            $this->consumeExpression();
        }
    }

    protected function consumeExpression()
    {
        $string = $this->readUnless(':');
        $parameter = $this->readUnlessEndOrWhitespace();
        $this->emit('definition', [$string, $parameter]);
        $this->skipWhitespace();
    }

    protected function readUnless($char)
    {
        $start = $this->position;
        $length = 0;
        while ($char !== ($current = $this->requireNextCharacter())) {
            if ($current === '\\') {
                $this->position++;
                $length++;
            }

            $length++;
        }
        return \substr($this->string, $start, $length);
    }

    protected function readUnlessEndOrWhitespace()
    {
        $start = $this->position;
        $length = 0;
        while ($this->position < $this->length
            && ! preg_match('/[\r\n\s]/', ($current = $this->requireNextCharacter()))
        ) {
            if ($current === '\\') {
                $this->position++;
                $length++;
            }

            $length++;
        }
        return \substr($this->string, $start, $length);
    }

    protected function requireNextCharacter()
    {
        if ($this->position < $this->length) {
            return $this->string[$this->position++];
        } else {
            throw new \RuntimeException("Reached unexpected end of string");
        }
    }

    protected function skipWhitespace()
    {
        while ($this->position < $this->length
            && \preg_match('/[\r\n\s\t]/', $this->string[$this->position])
        ) {
            $this->position++;
        }
    }
}
