<?php
/**
 * ZF2 Phpunit Runner
 *
 * @link      https://github.com/waltzofpearls/zf2-mongo-tailog for the canonical source repository
 * @copyright Copyright (c) 2014 Topbass Labs (topbasslabs.com)
 * @author    Waltz.of.Pearls <rollie@topbasslabs.com, rollie.ma@gmail.com>
 */

namespace PhpunitRunner\Library\Mongo;

use Mongo;
use MongoId;
use Zend\ServiceManager\ServiceLocatorInterface;

class MongoDb
{
	public static $unitTestDB = null;
	public static $serviceLocator = null;

	static function connect(ServiceLocatorInterface $serviceLocator)
	{
		self::$serviceLocator = $serviceLocator;

		//setup initial vars
		$conf = self::$serviceLocator->get('Config');
		$connectString = '';

		//if we're unit testing
		if (self::$unitTestDB) {
			$connectString = 'mongodb://'.$conf['mongodb']['tailog']['server'].':'
				.$conf['mongodb']['tailog']['port'].'/'.self::$unitTestDB;
		}

		//if not, use config vars
		else {
			$connectString = 'mongodb://'.$conf['mongodb']['tailog']['server'].':'
				.$conf['mongodb']['tailog']['port'].'/'
				.$conf['mongodb']['tailog']['database'];
		}

		//try to establish a connection
		$m = new Mongo($connectString, array("replicaSet" => true));
		self::$serviceLocator->set('MongoConn', $m);

		return $m;
	}

	static function selectDb()
	{
		$config = self::$serviceLocator->get('Config');
		$dbName = $config['mongodb']['tailog']['database'];
		if (self::$unitTestDB) {
			$dbName = self::$unitTestDB;
		}

		$db = self::$serviceLocator->get('MongoConn')->{$dbName};
		self::$serviceLocator->set('MongoDB', $db);

		return $db;
	}

	static function insert($table, $object)
	{
		$collection = self::$serviceLocator->get('MongoDB')->{$table};
		$response = $collection->insert($object, array("safe" => true));
		return array($response, $object);
	}

	static function findOne($table, $criteria)
	{
		$collection = self::$serviceLocator->get('MongoDB')->{$table};

		if (isset($criteria['id'])) {
			$criteria['_id'] = new MongoId($criteria['id']);
			unset($criteria['id']);
		}
		$responseObj = $collection->findOne($criteria);

		//don't want the id
		if (isset($responseObj['_id'])) {
			$responseObj['id'] = $responseObj['_id']->__toString();
			unset($responseObj['_id']);
		}

		return $responseObj;
	}

	static function find($table, $criteria, $sort = array(), $limit = null)
	{
		$collection = self::$serviceLocator->get('MongoDB')->{$table};

		if (isset($criteria['id'])) {
			$criteria['_id'] = new MongoId($criteria['id']);
			unset($criteria['id']);
		}
		$cursor = null;
		if ($limit === null) {
			$cursor = $collection->find($criteria)->sort($sort);
		}
		else {
			$cursor = $collection->find($criteria)->sort($sort)->limit($limit);
		}
		$cursor->rewind();

		$resultObj = array();
		foreach ($cursor as $doc) {
			$doc['id'] = $doc['_id']->__toString();
			unset($doc['_id']);

			foreach ($doc as $key => $value) {

				//we're in PHP so let's assume we don't want MongoDate objects
				if (gettype($value) == 'object') {

					//if it's a mongo date, lets convert it to a string date
					if (get_class($value) == 'MongoDate') {
						$doc[$key] = date('Y-m-d H:i:s', $value->sec);
					}
				}
			}
			$resultObj[] = $doc;
		}

		return $resultObj;
	}

	static function update($table, $criteria, $object)
	{
		$collection = self::$serviceLocator->get('MongoDB')->{$table};
		$responseObj = $collection->update($criteria, array('$set' => $object), array('safe' => true));
		return $responseObj;
	}

	static function distinct($table, $key)
	{
		$distincts = self::$serviceLocator
			->get('MongoDB')
			->command(array("distinct" => $table, "key" => $key));
		return $distincts['values'];
	}
}
