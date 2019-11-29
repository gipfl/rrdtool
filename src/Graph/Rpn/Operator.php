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

        Pow::NAME  => Pow::class,
        Sin::NAME  => Sin::class,
        Cos::NAME  => Cos::class,
        Log::NAME  => Log::class,
        Exp::NAME  => Exp::class,
        Sqrt::NAME => Sqrt::class,

        Atan::NAME    => Atan::class,
        Atan2::NAME   => Atan2::class,
        Floor::NAME   => Floor::class,
        Ceil::NAME    => Ceil::class,
        Deg2Rad::NAME => Deg2Rad::class,
        Rad2Deg::NAME => Rad2Deg::class,
        Abs::NAME     => Abs::class,

        // SetOperator (Set Operations)
        Sort::NAME    => Sort::class,
        Reverse::NAME => Reverse::class,
        Average::NAME => Average::class,
        SetMin::NAME  => SetMin::class,
        SetMax::NAME  => SetMax::class,
        Median::NAME  => Median::class,
        StandardDeviation::NAME => StandardDeviation::class,
        Percent::NAME => Percent::class,
        Trend::NAME   => Trend::class,

        // TODO: PREDICT, PREDICTSIGMA, PREDICTPERC
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
