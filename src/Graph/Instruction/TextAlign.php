<?php

namespace gipfl\RrdTool\Graph\Instruction;

/**
 * From `man rrdgraph_graph`:
 *
 * Labels are placed below the graph. When they overflow to the left, they wrap
 * to the next line. By default, lines are justified left and right.
 *
 * The TEXTALIGN function lets you change this default. This is a command and
 * not an option, so that you can change the default several times in your
 * argument list.
 */
class TextAlign extends Instruction
{
    const LEFT      = 'left';
    const RIGHT     = 'right';
    const JUSTIFIED = 'justified';
    const CENTER    = 'center';

    const VALID_ALIGNMENTS = [
        self::LEFT,
        self::RIGHT,
        self::JUSTIFIED,
        self::CENTER,
    ];

    /** @var string */
    protected $alignment;

    public function __construct($alignment)
    {
        $this->setAlignment($alignment);
    }

    /**
     * @return string
     */
    public function getAlignment()
    {
        return $this->alignment;
    }

    /**
     * @param string $alignment
     * @return $this
     */
    public function setAlignment($alignment)
    {
        self::assertValidAlignment($alignment);
        $this->alignment = $alignment;

        return $this;
    }

    public static function assertValidAlignment($alignment)
    {
        if (! \in_array($alignment, self::VALID_ALIGNMENTS)) {
            throw new \InvalidArgumentException(
                "$alignment is not a valid text alignment"
            );
        }
    }

    public function render()
    {
        return 'TEXTALIGN:' . $this->getAlignment();
    }
}
