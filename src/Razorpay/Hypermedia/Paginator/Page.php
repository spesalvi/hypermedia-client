<?php

namespace Razorpay\Hypermedia\Paginator;

class Page
{
	private $link;
	private $relation;

	public function __construct($link, $relation)
	{
		$link = trim($link);
		$this->link = substr($link, 1, -1);
		$this->relation = $relation;
	}

	public function isFirst()
	{
		return trim($this->relation) == 'rel="first"';
	}

	public function isLast()
	{
		return trim($this->relation) == 'rel="last"';
	}

	public function isNext()
	{
		return trim($this->relation) == 'rel="next"';
	}

	public function isPrev()
	{
		return trim($this->relation) == 'rel="prev"';
	}
	
	public function getLink()
	{
		return $this->link;
	}


}

