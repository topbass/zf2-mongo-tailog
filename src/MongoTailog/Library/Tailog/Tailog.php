<?php
/**
 * ZF2 Mongo Tailog
 *
 * @link      https://github.com/waltzofpearls/zf2-mongo-tailog for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

namespace MongoTailog\Library\Tailog;

use MongoDate;
use MongoCursorException;
use MongoTailog\Library\Mongo\MongoDb;
use MongoTailog\Library\Tailog\Formatter\FormatterInterface;
use MongoTailog\Library\Tailog\Formatter\SimpleFormatter;

class Tailog {
    private $mongoDb;

    public $criterion = array();
    public $level = null;
    public $verbose = false;
    public $dateFrom = null;

    public function __construct(MongoDb $mongo)
    {
        $this->mongoDb = $mongo;
    }

    public function tail()
    {
        $mongoDb = $this->mongoDb;

        $formatter = new SimpleFormatter();
        $formatter->verbose = $this->verbose;

        // Fetch the previous 5 entries
        $logs = $mongoDb->logs;

        print "Using mongo collection \"logs\" which has " . $logs->count() . " documents.\n";

        $levels = array('debug', 'info', 'warn', 'error', 'fatal');
        if ($this->level == 'info') {
            $levels = array('info', 'warn', 'error', 'fatal');
        } else if ($this->level == 'warn') {
            $levels = array('warn', 'error', 'fatal');
        } else if ($this->level == 'error') {
            $levels = array('error', 'fatal');
        }
        $query = array('level' => array('$in' => $levels));
        $limit = 10;
        if ($this->dateFrom) {
            print "Tailing from: ".date('r', $this->dateFrom) . "\n";
            $query['date'] = array('$gte' => new MongoDate($this->dateFrom));
            $limit = 10000;
        }

        foreach ($this->criterion as $criteria) {
            $matchType = $criteria[0];
            list($key, $value) = explode('=', substr($criteria, 1));
            $query[$key] = $value;
        }

        $cursor = $logs->find($query)->sort(array('$natural' => -1))->limit($limit);

        $lastDate = time();
        $docs = array();
        while($cursor->hasNext()) {
            $doc = $cursor->getNext();
            $docs[] = $doc;
        }
        $docs = array_reverse($docs);
        foreach ($docs as $doc) {
            $formatter->format($doc);
            $lastDate = $doc['date']->sec;
        }

        if ($this->follow) {
            $this->tailFrom($logs, $lastDate, $query, $formatter);
        }
    }


    function tailFrom($coll, $lastDate, $query, FormatterInterface $formatter)
    {
        if (!$lastDate) {
            $cursor = $coll->find($query)->sort(array('$natural' => -1))->limit(1);
            $lastDate = time();
            if ($cursor->hasNext()) {
                $doc = $cursor->getNext();
                $lastDate = $doc['date']->sec;
            }
        }

        $lastMongoDate = new MongoDate($lastDate);
        $cursor = $coll->find(
            array_merge(
                $query, array(
                    'date' => array(
                        '$gte' => $lastMongoDate
                    )
                )
            )
        );

        try {
            $cursor = $cursor->tailable(true);
            while (true) {
                if ($cursor->hasNext()) {
                    $doc = $cursor->getNext();
                    $formatter->format($doc);
                } else {
                    sleep(1);
                }
            }
        } catch(MongoCursorException $e) {
            print "ERROR: " . $e->getMessage() . "\n";
        }
    }
}
