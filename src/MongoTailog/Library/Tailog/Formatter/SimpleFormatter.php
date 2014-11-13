<?php
/**
 * ZF2 Mongo Tailog
 *
 * @link      https://github.com/waltzofpearls/zf2-mongo-tailog for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

namespace MongoTailog\Library\Tailog\Formatter;

class SimpleFormatter implements FormatterInterface
{
    public $verbose = false;

    function format($entry)
    {
        $d = $entry;
        $verbose = $this->verbose;

        $date = date('Y-m-d H:i:s', (isset($d['date']) && is_object($d['date'])) ? $d['date']->sec : -1);
        $level = isset($d['level']) ? $d['level'] : '[null]';
        $ts = str_repeat(' ', 4);
        $caller = '';
        if (isset($d['caller'])) {
            $caller = $d['caller']['file'] . ':' . $d['caller']['line'];
        }

        $level = str_pad(strtoupper($level), 1, ' ');
        if ($verbose) {
            $props = array();
            if ($d['context']) {
                foreach($d['context'] as $key=>$val)
                    $props[] = "$key=$val";
            }
            $context = implode(',', $props);
            print "$date $level $caller $context\n";
            print "$ts$d[message]\n";
        } else {
            $message = $d["message"];
            // Add some padding for multiline logs.
            $message = str_replace("\n", "\n    ", $message);

            print "$date $level $caller $message\n";
        }

        if (isset($d['exception']) && $d['exception']) {
            $message = isset($d['exception']['message']) ? $d['exception']['message'] : null;
            $className = $d['exception']['className'];
            print "$ts$className: $message\n";
            print $ts . "thrown from " . $d['exception']['file'] . ' (' . $d['exception']['line'] . "):\n";

            for ($i=0;$i < count($d['exception']['stackTrace']); $i++) {
                print "$ts" . $d['exception']['stackTrace'][$i] . "\n";
            }
        }
    }
}
