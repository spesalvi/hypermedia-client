<?php

namespace Razorpay;
require_once 'vendor/autoload.php';


use Guzzle\Http;


class Client implements \ArrayAccess
{

	const ACTION_UNSUPPORTED = 'This action is not supported';

	private $_apis;

	public function __construct($api_endpoint)
	{
		if(!$api_endpoint)
			return;	
		$http_client = new \GuzzleHttp\Client([
			'base_uri' => $api_endpoint
		]);
		$response = $http_client->request('GET', '')
				->getBody()
				->getContents();

		$this->_apis = json_decode($response);
	}
	
	public function __get($var)
	{
			
	}

	public function __call($name, $arguments)
	{
		$url = $this->_apis->{$name . '_url'};
		if($url == null)
		{
			return;
		}

		foreach($arguments as $argument)
		{
			$url = preg_replace('/{user}/', $argument, $url);
		}
		return new Client($url);

	}

	public function offsetSet($offset, $value)
	{
		throw new UnsupportedException(self::ACTION_UNSUPPORTED);
	}

	public function offsetExists($offset)
	{
		return is_array($this->_apis) && 
			isset($this->_apis[$offset]);
	}


	public function offsetUnset($offset)
	{
		throw new UnsupportedException(self::ACTION_UNSUPPORTED);	
	}

	public function offsetGet($offset)
	{
		return is_array($this->_apis) && isset($this->_apis[$offset]) ? 
			$this->_apis[$offset] : 
			null;
	}
}


$client = new Client('https://api.github.com/');

$user = $client->user('spesalvi');

$repos = $user->repos();

var_dump($repos[0]);
