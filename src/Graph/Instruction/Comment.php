<?php

namespace gipfl\RrdTool\Graph\Instruction;

/**
 * From `man rrdgraph_graph`:
 *
 * Text is printed literally in the legend section of the graph. Note that in
 * RRDtool 1.2 you have to escape colons in COMMENT text in the same way you
 * have to escape them in *PRINT commands by writing '\:'.
 */
class Comment extends Instruction
{
    /** @var string */
    protected $text;

    public function __construct($text)
    {
        $this->setText($text);
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    public function render()
    {
        return 'COMMENT:' . $this->getText();
    }
}
