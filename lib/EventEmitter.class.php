<?php

class EventEmitter {

	private $events = array();	

	public function EventEmitter() {

	}
	
	/*** 
	* @param obj (Object or Null) if $callback is not a method of $obj, $obj needs to be null
	* @param callback (String) name of the method or function
	**/
	public function on($event, $obj, $callback) {
		if (!$callback) {
			return Log::warn('EventEmitter::on > invalid callback');
		}
		if (!isset($this->events[$event])) {
			$this->events[$event] = array();
		}
		if ($obj && !method_exists($obj, $callback)) {
			return Log::warn('EventEmitter::on > callback method is invalid');
		}
		$this->events[$event][] = array(
			'obj' => $obj,
			'callback' => $callback,
			'persistent' => true
		);
	}

	/*** 
	* @param obj (Object or Null) if $callback is not a method of $obj, $obj needs to be null
	* @param callback (String) name of the method or function
	**/
	public function once($event, $obj, $callback) {
		if (!$callback) {
			return Log::warn('EventEmitter::once > invalid callback');
		}
		if (!isset($this->events[$event])) {
			$this->events[$event] = array();
		}
		if ($obj && !method_exists($obj, $callback)) {
			return Log::warn('EventEmitter::once > callback method is invalid');
		}
		$this->events[$event][] = array(
			'obj' => $obj,
			'callback' => $callback,
			'persistent' => false
		);
	}

	public function remove($event, $obj, $callback) {
		foreach ($this->events as $eventName => $handlers) {
			for ($i = 0, $len = count($handlers); $i < $len; $i++) {
				$handler = $handlers[$i];
				if ($event === $eventName && $obj === $handler['obj'] && $callback === $handler['callback']) {
					array_splice($handlers, $i, 1);
					$this->events[$eventName] = $handlers;
					break;
				}
			}
		}
	}

	public function removeAll($eventName = null) {
		if ($eventName) {
			$this->events[$eventName] = array();
		} else {
			$this->events = array();
		}
	}

	public function emit($event) {
		$handlers = (isset($this->events[$event])) ? $this->events[$event] : null;
		if (!$handlers) {
			return;
		}
		$args = func_get_args();
		array_splice($args, 0, 1);
		for ($i = 0, $len = count($handlers); $i < $len; $i++) {
			$handler = $handlers[$i];
			// execute the callback
			if ($handler['obj']) {
				// with object
				call_user_func_array(array($handler['obj'], $handler['callback']), $args);	
			} else {
				// a function
				call_user_func_array($handler['callback'], $args);	
			}
			// if handler is not persistent, remove it
			if (!$handler['persistent']) {
				array_splice($handlers, $i, 1);
				$thos->event[$event] = $handlers;
			}
		}
	}		
}

// global event emitter
class GlobalEvent {
	
	private static $eventEmitter;	

	public static function on($event, $obj, $callback) {
		self::$eventEmitter->on($event, $obj, $callback);
	}
	
	public static function once($event, $obj, $callback) {
		self::$eventEmitter->once($event, $obj, $callback);
	}
	
	public static function remove($event, $obj, $callback) {
		self::$eventEmitter->remove($event, $obj, $callback);
	}

	public static function removeAll($event) {
		self::$eventEmitter->removeAll($event);
	}
	
	public static function emit($event, $params) {
		self::$eventEmitter->emit($event, $params);
	}
	
	// never call this method outside of this file
	public static function setup() {
		self::$eventEmitter = new EventEmitter();
	}	

}

GlobalEvent::setup();

?>
