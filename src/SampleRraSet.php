<?php

namespace gipfl\RrdTool;

class SampleRraSet
{
    // PNP default RRA config
    // you will get 400kb of data per datasource
    protected static $defaultCreate = [
        // 2880 entries with 1 minute step = 48 hours
        'RRA:AVERAGE:0.5:1:2880',
        // 2880 entries with 5 minute step = 10 days
        'RRA:AVERAGE:0.5:5:2880',
        // 4320 entries with 30 minute step = 90 days
        // 'RRA:AVERAGE:0.5:30:4320',
        // 5840 entries with 360 minute step = 4 years
        // 'RRA:AVERAGE:0.5:360:5840',
        'RRA:MAX:0.5:1:2880',
        'RRA:MAX:0.5:5:2880',
        // 'RRA:MAX:0.5:30:4320',
        // 'RRA:MAX:0.5:360:5840',
        'RRA:MIN:0.5:1:2880',
        'RRA:MIN:0.5:5:2880',
        // 'RRA:MIN:0.5:30:4320',
        // 'RRA:MIN:0.5:360:5840'
    ];

    protected static $kickstartWithSeconds = [
        // 4 hours of one per second (346kB)
        // 'RRA:AVERAGE:0.5:1:14400',
        // 'RRA:MAX:0.5:1:14400',
        // 'RRA:MIN:0.5:1:14400',
        // 2 days of four per minute (1 every 15s) (+277kB)
        'RRA:AVERAGE:0.5:15:11520',
        'RRA:MAX:0.5:15:11520',
        'RRA:MIN:0.5:15:11520',
    ];

    protected static $fullWithSeconds = [
        // 4 hours of one per second (346kB)
        'RRA:AVERAGE:0.5:1:14400',
        'RRA:MAX:0.5:1:14400',
        'RRA:MIN:0.5:1:14400',
        // 2 days of four per minute (1 every 15s) (+277kB)
        'RRA:AVERAGE:0.5:15:11520',
        'RRA:MAX:0.5:15:11520',
        'RRA:MIN:0.5:15:11520',
        // 2880 entries with 5 minute step = 10 days (+60kB)
        'RRA:AVERAGE:0.5:300:2880',
        'RRA:MAX:0.5:300:2880',
        'RRA:MAX:0.5:300:2880',
        // 4320 entries with 30 minute step = 90 days
        'RRA:AVERAGE:0.5:1800:4320',
        'RRA:MIN:0.5:1800:4320',
        'RRA:MAX:0.5:1800:4320',
        // 5840 entries with 360 minute step = 4 years
        'RRA:AVERAGE:0.5:21600:5840',
        'RRA:MIN:0.5:21600:5840',
        'RRA:MAX:0.5:21600:5840',
    ];

    // Faster RRA config
    protected static $fasterCreate = [
        // 1800 entries with 1 second step = 30 minutes
        'RRA:AVERAGE:0.5:1:1800',
        // 2880 entries with 1 minute step = 48 hours
        'RRA:AVERAGE:0.5:60:2880',
        // 2880 entries with 5 minute step = 10 days
        'RRA:AVERAGE:0.5:300:2880',
        // 4320 entries with 30 minute step = 90 days
        'RRA:AVERAGE:0.5:1800:4320',
        // 5840 entries with 360 minute step = 4 years
        'RRA:AVERAGE:0.5:21600:5840',
        'RRA:MAX:0.5:60:2880',
        'RRA:MAX:0.5:300:2880',
        'RRA:MAX:0.5:1800:4320',
        'RRA:MAX:0.5:21600:5840',
        'RRA:MIN:0.5:60:2880',
        'RRA:MIN:0.5:300:2880',
        'RRA:MIN:0.5:1800:4320',
        'RRA:MIN:0.5:21600:5840'
    ];

    /**
     * @return RraSet
     */
    public static function faster()
    {
        return new RraSet(static::$fasterCreate);
    }

    /**
     * @return RraSet
     */
    public static function pnpDefaults()
    {
        return new RraSet(static::$defaultCreate);
    }

    /**
     * @return RraSet
     */
    public static function kickstartWithSeconds()
    {
        return new RraSet(static::$kickstartWithSeconds);
    }
}
