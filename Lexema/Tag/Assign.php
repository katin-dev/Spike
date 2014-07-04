<?php 
namespace Spike\Lexema\Tag;

class Assign extends \Spike\Lexema\Tag {
	public function parse(&$data) {
		$params = $this->getParams($data);
		if(isset($params['var'])) {
			$html = "";
			foreach ($this->getTags() as $tag) {
				$html .= $tag->parse($data);
			}
			$data[$params['var']] = $html;
		}
	}
}