<?php
class Lexema_Loop extends Lexema_Tag {
	public function parse($data) {
		
		$html = "";
		if(isset($data[$this->getName()])) {
			/* массив,  которым работаем */
			$array = $data[$this->getName()];
			if(is_array($array)) {
				$arrayLength = count($array);
				$position = 0;
				foreach ($array as $item) {
					
					$itemData = array();
					
					/* хотим иметь доступ к переменным уровня выше */
					foreach ($data as $key => $value) {
						if($key != $this->getName()) {
							$itemData[$key] = $value;
						}
					}
					
					/* нам нужны вспомогательные переменные */
					$itemData['_is_first_'] = (string) ($position == 0);
					$itemData['_is_last_'] = (string) ($position == $arrayLength - 1);
					$itemData['_pos_'] = $position;

					$itemData = array_merge($itemData, $item);
					
					foreach ($this->getTags() as $tag) {
						$html .= $tag->parse($itemData);
					}
					
					$position ++;
				}
			}
		}
		
		return $html;
	}
	
	private function itemData($data, $key) {
		unset($itemData[$this->getName()]);
	}
}

?>