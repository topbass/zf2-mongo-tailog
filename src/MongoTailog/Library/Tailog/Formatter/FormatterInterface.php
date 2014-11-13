<?php
/**
 * ZF2 Mongo Tailog
 *
 * @link      https://github.com/waltzofpearls/zf2-mongo-tailog for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

namespace MongoTailog\Library\Tailog\Formatter;

interface Formatter
{
    function format($entry);
}
