<?php

namespace gipfl\RrdTool\Graph\Rpn;

class Operator
{
    const KNOWN_OPERATORS = [
        // BooleanOperator
        Equal::NAME          => Equal::class,
        GreaterOrEqual::NAME => GreaterOrEqual::class,
        GreaterThan::NAME    => GreaterThan::class,
        LessOrEqual::NAME    => LessOrEqual::class,
        LessThan::NAME       => LessThan::class,
        NotEqual::NAME       => NotEqual::class,

        IsInfinity::NAME     => IsInfinity::class,
        IsUnknown::NAME      => IsUnknown::class,

        IfElse::NAME         => IfElse::class,

        // CompareValuesOperator
        Min::NAME    => Min::class,
        MinNan::NAME => MinNan::class,
        Max::NAME    => Max::class,
        MaxNan::NAME => MaxNan::class,
        Limit::NAME  => Limit::class,

        // ArithmeticOperator
        Add::NAME      => Add::class,
        AddNan::NAME   => AddNan::class,
        Subtract::NAME => Subtract::class,
        Multiply::NAME => Multiply::class,
        Divide::NAME   => Divide::class,
        Modulo::NAME   => Modulo::class,
    ];

    // Not sure about this:
    // /** @var string Nice name */
    // protected $label;

    /**
     * The number of elements this operator demands from the stack. If null,
     * one more element is fetched to determine the number of required elements
     * for operators allowing for a variable amount of elements
     *
     * @var int|null
     */
    protected $requiredElements;
}
