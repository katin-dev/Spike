<?php 
namespace Spike\Lexema\Tag;

class Assign extends \Spike\Lexema\Tag {
	public function parse(&$data) {
		$params = $this->getParams($data);
		if(isset($params['var'])) {
			$html = "";
			$tags = $this->getTags();
			
			// Позволяем делать записи вроде {{ set var="goods" }} {{module.catalog.getFreshGoods }} {{/set}}
			if(isset($params['raw'])) {
				// Сперва удаляем текстовые теги
				foreach ($tags as $key => $tag) {
					if($tag instanceof \Spike\Lexema\Text) {
						unset($tags[$key]);
					}
				}
			}
			
			$tags = array_values($tags);
			$tagsCount = count($tags);
			
			if($tagsCount == 1) {	// Если после очистки остался один единственный тег, то можно получить от него сырые данные (массив, например)
				$tag = $this->mutate($tags[0], $data);
				if($tag instanceof \Spike\Lexema\Tag\Callback && isset($params['raw'])) {
					$tag->returnRawResult(true);
					$html = $tag->parse($data);
				} elseif(isset($params['raw'])) {
					$html = $this->getVariableValue($tag->getName(), $data, $found);
				} else {
					$html = $tag->parse($data);
				}
			} else {
				// Считаем, что каждый внутренний тег должен вернуть HTML
				foreach ($this->getTags() as $tag) {
					$html .= $tag->parse($data);
				}
			}
			$data[$params['var']] = $html;
		}
	}
}