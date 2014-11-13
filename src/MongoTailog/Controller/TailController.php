<?php
/**
 * ZF2 Mongo Tailog
 *
 * @link      https://github.com/waltzofpearls/zf2-mongo-tailog for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

namespace MongoTailog\Controller;

use MongoTailog\Library\Mongo\MongoDb;
use MongoTailog\Library\Tailog\Tailog;
use MongoTailog\Library\Mvc\Controller\AbstractConsoleController;

class TailController extends AbstractConsoleController
{
    public function runAction()
    {
        $request = $this->getRequest();
        $level = strtolower($request->getParam('level', 'debug'));
        $dateFrom = $request->getParam('date-from', null);
        $nofollow = !!$request->getParam('nofollow');
        $matches = explode(',', $request->getParam('match', ''));

        if (!is_null($dateFrom)) {
            $dateFrom = strtotime($dateFrom);
        }

        $criterion = array();
        foreach($matches as $match) {
            $criterion[] = "=$match";
        }

        $mongoConn = MongoDb::connect($this->getServiceLocator());
        $mongoDb = MongoDb::selectDb();

        $tailog = new Tailog($mongoDb);
        $tailog->follow = !$nofollow;
        $tailog->criterion = $criterion;
        $tailog->level = $level;
        $tailog->dateFrom = $dateFrom;
        $tailog->tail();
    }

    public function usageAction()
    {
        //
    }
}
