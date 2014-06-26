<?php
class Lexema_Condition extends Lexema_Tag {

	public function parse($data) {
		if(preg_match('/([\.\w]+)\s*([=><])?\s*(["\'\w\.]+)?/', $this->getParamsString(), $m)) {
			$varname = $m[1];
			$varvalue = $this->getVariableValue($varname, $data);
			$varvalue = empty($varvalue) ? '0' : $varvalue;
			
			if(isset($m[2])) {
				$sign = $m[2];
				$valueName = $m[3];
				
				if(preg_match('/["\']([\.\w]+)["\']/', $valueName)) {
					/* 'строка' или "строка" */
					$value = $valueName;
				} else {					
					if(is_numeric($valueName)) {
						/* 5 или 10 или 7.5 */
						$value = $valueName;
					} elseif(($value = $this->getVariableValue($valueName, $data)) !== null) {
						/* age=25 или name="Сергей"  - если число, то кавычки не нужны, если строка, то нужны */
						if(is_numeric($value)) {
							//$value = $data[$valueName];
						} elseif (is_string($data[$valueName])) {
							$value = '"'.$value.'"';
						} else {
							/* ещё бывают объекты, массивы... но мы их не обрабатываем */
							$value = '"'.gettype($data[$valueName]).'"';
						}
					} else {
						$value = '"'.$valueName.'"';
					}
				}
				$condition = "$varvalue $sign $value";
			} else {
				$condition = $varvalue ? "true" : "false";
				$condition = $condition ? $condition : "false";
			}
			
			$phpCode = "return $condition ? 1 : 0;";
			
			$conditionResult = eval($phpCode);
			
			$html = "";
			
			/* разберем все лексемы на те, которые должны выполниться в случае успеха и в случае неудачи */
			$conditionTags = array(
				'true' => array(), 
				'false' => array()
			);
			
			$tagsName = "true";
			
			foreach ($this->getTags() as $tag) {
				if($tag->getName() == 'else') {
					$tagsName = 'false';
				}
				$conditionTags[$tagsName][] = $tag;
			}
			
			/* выбираем лексемы согласно выполненному условию оператора if */
			if($conditionResult) {
				$tags = $conditionTags['true'];
			} else {
				$tags = $conditionTags['false'];
			}
			
			foreach ($tags as $tag) {
				$html .= $tag->parse($data);
			}
			
			return $html;
		}
	}
}

?>