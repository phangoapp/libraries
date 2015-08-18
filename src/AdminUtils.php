<?php

/**
* A simple class with utils for the admin.
*/

namespace PhangoApp\PhaLibs;

use PhangoApp\PhaRoutes\Routes;

class AdminUtils {

	static public function set_admin_link($text_admin, $parameters)
	{
	
		return Routes::make_module_url(ADMIN_FOLDER, 'index', 'index', array($text_admin), $get=$parameters);

	}

}

?>