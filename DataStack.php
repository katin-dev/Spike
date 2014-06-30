<?php

class DataStack
{
	/**
	 * Стек данных.
	 * @var array
	 */
	private $dataStack = array();
	
	public function pushData($data) {
		array_push($this->dataStack, $data);
	}
	public function popData() {
		return array_pop($this->dataStack);
	}
	public function getData() {
		$data = array();
		foreach ($this->dataStack as $dataPart) {
			$data = array_merge($data, $dataPart);
		}
		return $data;
	}
}

?>