<?php
namespace Spike;
/**
 * Класс для замера времени выполнения парсера (или чего-то другого)
 * @author Gourry
 *
 */
class Timer
{
	private static $counter = 0;
	private static $intervals = array();
	private static $intervalsFlat = array();
	
	public static function start($label = null) {
		self::$counter ++;
		$label = $label ? $label : self::$counter;
		array_push(self::$intervals, array($label, microtime(true), null));
	}
	
	public static function stop() {
		list($label, $start, $pausedTime) = array_pop(self::$intervals);
		$stop = microtime(true);
		$time = $stop - $start - $pausedTime;
		self::$intervalsFlat[] = array($label, $start, $stop, $time);
	}
	
	public static function pause() {
		$length = count(self::$intervals);
		self::$intervals[$length - 1][2] = microtime(true);
	}
	
	public static function resume() {
		$length = count(self::$intervals);
		self::$intervals[$length - 1][2] = microtime(true) - self::$intervals[$length - 1][2];
	}
	
	public static function getIntervalsPrintable() {
		$str = '<table style="font-size:12px;font-family:monospace;">';
		foreach(self::$intervalsFlat as $interval) {
			$str .= '<tr><td>'.$interval[0].'</td><td>'.round($interval[3], 4).'</td></tr>';
		}
		$str .= '</table>';
		return $str;
	}
	
	public static function getTime() {
		$t = 0;
		foreach (self::$intervalsFlat as $interval) {
			$t += $interval[3];
		}
		return $t;
	}
}
