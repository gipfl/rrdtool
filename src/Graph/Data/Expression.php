<?php

namespace gipfl\RrdTool\Graph\Data;

class Expression extends Definition
{
    protected $tag;

    /** @var string */
    protected $expression;

    public function __construct($vname, $expression)
    {
        $this->setVariableName($vname);
        $this->setExpression($expression);
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param string $expression
     * @return Expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }

    protected function render()
    {
        return $this->tag . ':' . $this->getVariableName() . '=' . $this->getExpression();
    }
}
