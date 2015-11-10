<?php

namespace Razorpay\Hypermedia;
require_once 'Paginator/Page.php';
require_once 'Paginator/Paginator.php';

use Guzzle\Http;
class Client implements \ArrayAccess
{

	const ACTION_UNSUPPORTED = 'This action is not supported';
	const ITEMS_PER_PAGE = 30;

	private $_apis;
	private $_paginate = false;
	private $_page_nums = 0;
	private $_paginator;

	public function __construct($url)
	{
		if(!$url)
			return;	
		list($responseJson, $response) = $this->fetchResponse($url);
		$this->_apis = json_decode($responseJson);
		
		$this->_paginate = $this->willPaginate($response);
		if($this->_paginate)
			$this->_page_nums = $this->getNumOfPages($response);

	}
	
	public function __get($var)
	{
		if(isset($this->_apis[$var]))
			return $this->_apis[$var];
		return null;
	}

	public function __call($name, $arguments)
	{
		$url = $this->_apis->{$name . '_url'};
		if($url == null)
			return;

		foreach($arguments as $argument)
		{
			$url = preg_replace('/{[a-z]+}/', $argument, $url, 1);
		}
		return new Client($url);
	}

	public function offsetSet($offset, $value)
	{
		throw new UnsupportedException(self::ACTION_UNSUPPORTED);
	}

	public function offsetExists($offset)
	{
		if($this->_paginate)
			return $offset <= self::ITEMS_PER_PAGE * $this->_page_nums;
		return false;
	}


	public function offsetUnset($offset)
	{
		throw new UnsupportedException(self::ACTION_UNSUPPORTED);	
	}

	public function offsetGet($offset)
	{
		if(!$this->_paginate)
			return null;
		while(count($this->_apis) < $offset)
		{
			$next = $this->fetchNextPage();
			$this->_apis = array_merge($this->_apis, $next);
		}
		
		return $this->_apis[$offset];
	}


	private function fetchResponse($api_url)
	{
		$http_client = new \GuzzleHttp\Client([
			'base_uri' => $api_url,
		]);
		$response = $http_client->request('GET', '');
	        $responseJson =	$response->getBody()
				->getContents();
		return array($responseJson, $response);
	}

	private function getNumOfPages($response)
	{
		$linkHeader = $response->getHeader('Link');
		if(!$linkHeader && is_array($this->_apis))
			return 1;

		$this->_paginator = New Paginator\Paginator($linkHeader[0]);
		return $this->_paginator->getNumOfPages();
	}

	private function fetchNextPage()
	{
		$url = $this->_paginator->getNextLink();
		list($responseJson, $response) = $this->fetchResponse($url);
		return json_decode($responseJson);
	}

	private function willPaginate($response)
	{
		return $response->hasHeader('Link') || 
			is_array($this->_apis);	
	}
}
