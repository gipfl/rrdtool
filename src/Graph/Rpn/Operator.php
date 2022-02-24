<?php

namespace gipfl\RrdTool\Graph\Rpn;

use InvalidArgumentException;

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

        // SpecialValue
        Unknown::NAME => Unknown::class,
        NegativeInfinite::NAME => NegativeInfinite::class,
        Previous::NAME => Previous::class,
        // TODO: Prev(vname)
        Count::NAME => Count::class,

        // TimeValue
        Now::NAME => Now::class,
        StepWidth::NAME => StepWidth::class,
        NewDay::NAME => NewDay::class,
        NewWeek::NAME => NewWeek::class,
        NewMonth::NAME => NewMonth::class,
        NewYear::NAME => NewYear::class,
        Time::NAME => Time::class,
        LocalTime::NAME => LocalTime::class,

        // StackOperator
        DuplicateTopStackElement::NAME => DuplicateTopStackElement::class,
        RemoveTopStackElement::NAME    => RemoveTopStackElement::class,
        ExchangeTopStackElements::NAME => ExchangeTopStackElements::class,
        Depth::NAME => Depth::class,
        Copy::NAME  => Copy::class,
        Index::NAME => Index::class,
        Roll::NAME  => Roll::class,

        // VariablesOperator
        Maximum::NAME => Maximum::class,
        Minimum::NAME => Minimum::class,
        StreamAverage::NAME => StreamAverage::class,
        // TODO: Dup. StreamStandardDeviation::NAME => StreamStandardDeviation::class,
        Last::NAME => Last::class,
        First::NAME => First::class,
        Total::NAME => Total::class,
        // TODO: Dup. StreamPercent::NAME => StreamPercent::class,
        StreamPercentNan::NAME => StreamPercentNan::class,
        LeastSquaresLineSlope::NAME => LeastSquaresLineSlope::class,
        LeastSquaresLineIntercept::NAME => LeastSquaresLineIntercept::class,
        LeastSquaresLineCorrelationCoefficient::NAME => LeastSquaresLineCorrelationCoefficient::class,
    ];

    // Not sure about this:
    // /** @var string Nice name */
    // protected $label;

    /**
     * The number of elements this operator demands from the stack. If null,
     * one more element is fetched to determine the number of required elements
     * for operators allowing for a variable amount of elements
     *
     * TODO: This is our arity. We have operators dealing with Sets and we have
     *       stack operators, both are a little bit... special. This needs improvement
     */
    protected ?int $requiredElements = null;

    public function getRequiredElements(): ?int
    {
        return $this->requiredElements;
    }

    public static function getClass(string $operatorName): string
    {
        if (isset(self::KNOWN_OPERATORS[$operatorName])) {
            return self::KNOWN_OPERATORS[$operatorName];
        }

        throw new InvalidArgumentException("'$operatorName' is not a known operator");
    }
}
