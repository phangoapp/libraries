<?php

/**
*
* @author  Antonio de la Rosa <webmaster@web-t-sys.com>
* @file
* @package PhaLibs
*
*
*/

namespace PhangoApp\PhaLibs;

use PhangoApp\PhaRouter\Routes;

/**
* A simple class for utilities used in admin
*/

class AdminUtils {

    /**
    * A simple method for create urls for use in admin
    * @param string $text_admin The module to admin
    * @param array $parameters An array with format key value used for set get values in the new url
    */

	static public function set_admin_link($text_admin, $parameters)
	{
	
		return Routes::make_module_url(ADMIN_FOLDER, 'index', 'home', array($text_admin), $get=$parameters);

	}

}

?>