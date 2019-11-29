<?php

namespace gipfl\RrdTool\Graph\Rpn;

/**
 * Create a "sliding window" average of another data series.
 *
 * Usage: CDEF:smoothed=x,1800,TREND
 *
 * This will create a half-hour (1800 second) sliding window average of x. The
 * average is essentially computed as shown here:
 *
 *                  +---!---!---!---!---!---!---!---!--->
 *                                                     now
 *                        delay     t0
 *                  <--------------->
 *                          delay       t1
 *                      <--------------->
 *                               delay      t2
 *                          <--------------->
 *
 * Value at sample (t0) will be the average between (t0-delay) and (t0)
 * Value at sample (t1) will be the average between (t1-delay) and (t1)
 * Value at sample (t2) will be the average between (t2-delay) and (t2)
 */
class Trend extends SetOperator
{
    const NAME = 'TREND';
}
