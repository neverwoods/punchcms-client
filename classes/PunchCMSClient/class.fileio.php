<?php

/**
 * 
 * File IO operations.
 * @author felix
 * @version 1.0
 *
 */
class FileIO {

	public static function extension($filename) {
		$path_info = pathinfo($filename);
    	return $path_info['extension'];
	}
	
	public static function add2Base($filename, $addition) {
		$strBase = basename($filename, self::extension($filename));
		return substr($strBase, 0, -1) . $addition . "." . self::extension($filename);
	}
	
	public static function unlinkDir($dir) {
	    $files = glob( $dir . '*', GLOB_MARK );
	    foreach( $files as $file ){
	        if( substr( $file, -1 ) == '/' )
	            self::unlinkDir( $file );
	        else
	            unlink( $file );
	    }
	   
	    if (is_dir($dir)) rmdir( $dir );
	} 
	
}