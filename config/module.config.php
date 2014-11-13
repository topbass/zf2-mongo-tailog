<?php
/**
 * ZF2 Mongo Tailog
 *
 * @link      https://github.com/waltzofpearls/zf2-mongo-tailog for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

return array(
    // Console router
    'console' => array(
        'router' => array(
            'routes' => array(
                'print-usage' => array(
                    'options' => array(
                        'route'    => '[--help|-h]:printUsage',
                        'defaults' => array(
                            'controller' => 'MongoTailog\Controller\Main',
                            'action'     => 'usage'
                        ),
                    ),
                ),
                'tailog-run' => array(
                    'options' => array(
                        'route'    => 'tailog [--env=] [--nofollow] [--level=] [--date-from=] [--match=] [--verbose|-v] [--help|-h]',
                        'defaults' => array(
                            'controller' => 'MongoTailog\Controller\Tail',
                            'action'     => 'run'
                        ),
                    ),
                ),
            ),
        ),
    ),
    // Controller and controller plugin
    'controllers' => array(
        'invokables' => array(
            'MongoTailog\Controller\Main' => 'MongoTailog\Controller\MainController',
            'MongoTailog\Controller\Tailog' => 'MongoTailog\Controller\TailogController',
        ),
    ),
    'mongodb' => array(
        'tailog' => array(
            'server' => 'localhost',
            'port' => '27017',
            'database' => 'topbass',
            'collection' => 'logs',
            'username' => 'topbass',
            'password' => file_get_contents('/private/mongodbPassword'),
        ),
    ),
);
