<?php

class FileIO {

	public static function extension($filename) {
		$path_info = pathinfo($filename);
    	return $path_info['extension'];
	}
	
	public static function add2Base($filename, $addition) {
		$strBase = basename($filename, self::extension($filename));
		return substr($strBase, 0, -1) . $addition . "." . self::extension($filename);
	}
	
}