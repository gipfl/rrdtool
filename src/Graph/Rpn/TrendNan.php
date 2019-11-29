<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * TRENDNAN is - in contrast to TREND - NAN-safe. If you use TREND and one
 * source value is NAN the complete sliding window is affected. The TRENDNAN
 * operation ignores all NAN-values in a sliding window and computes the
 * average of the remaining values.
 */
class TrendNan extends SetOperator
{
    const NAME = 'TRENDNAN';
}
