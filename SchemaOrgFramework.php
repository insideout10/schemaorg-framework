<?php

/**
 * This is the SchemaOrg Framework.
 * @package SchemaOrgFramework
 */

// the autoload code is deliberately taken from the log4php project.
if (function_exists('__autoload')) {
	trigger_error("SchemaOrgFramework: It looks like your code is using an __autoload() function. SchemaOrgFramework uses spl_autoload_register() which will bypass your __autoload() function and may break autoloading.", E_USER_WARNING);
}

spl_autoload_register(array('SchemaOrgFramework', 'autoload'));


/**
 * This is the SchemaOrgFramework entry class.
 * @package SchemaOrgFramework
 */
class SchemaOrgFramework {
	
	// the list of classes part of this framework, for autoloading.
	private static $_classes = array(
			'IItemRepository' => '/IItemRepository.php',
			'ISchema' => '/ISchema.php',
			'AudioObject' => '/schemas/AudioObject.php',
			'BlogPosting' => '/schemas/BlogPosting.php',
			'CreativeWork' => '/schemas/CreativeWork.php',
			'GeoCoordinates' => '/schemas/GeoCoordinates.php',
			'MediaObject' => '/schemas/MediaObject.php',
			'Movie' => '/schemas/Movie.php',
			'Organization' => '/schemas/Organization.php',
			'Other' => '/schemas/Other.php',
			'Person' => '/schemas/Person.php',
			'Place' => '/schemas/Place.php',
			'Product' => '/schemas/Product.php',
			'Thing' => '/schemas/Thing.php',
			'VideoObject' => '/schemas/VideoObject.php',
			'WordPressRepository' => '/wordpress/WordPressRepository.php'
			);
	
	// the list of schemas previously loaded.
	private static $_schemas;
	
	/**
	 * Class autoloader. This method is provided to be invoked within an
	 * __autoload() magic method.
	 * @param string $className The name of the class to load.
	 */
	public static function autoload($className) {
		if(isset(self::$_classes[$className])) {
			include dirname(__FILE__) . self::$_classes[$className];
		}
	}
	
	/**
	 * Returns the list of supported schemas.
	 * @return array An array of keys/values (Friendly Name => Schema) for the schemas.
	 */
	public static function getSchemas() {
		
		// if the list of schemas has already been loaded, then return it.
		if (null != self::$_schemas) {
			return self::$_schemas;
		}
		
		// load the schemas list and save it for future uses.
		$schemas = array();
		
		if ($handle = opendir(dirname(__FILE__) . '/schemas/')) {
		
			/* This is the correct way to loop over the directory. */
			while (false !== ($entry = readdir($handle))) {
				
				if ('php' == pathinfo($entry, PATHINFO_EXTENSION)) {
					$class = pathinfo($entry, PATHINFO_FILENAME);
					$schema = new $class();
					$schemas[$schema::getFriendlyName()] = $class; 
				}
			}
			
			closedir($handle);
		}
		
		self::$_schemas = $schemas;
		
		return self::$_schemas;
	}
	
	/**
	 * Checks if a schema is supported.
	 * @param string $schema The name of the schema to check for support.
	 * @return boolean True if the schema is supported otherwise false.
	 */
	public static function isSchemaSupported($schema) {
		return in_array($schema, array_values(self::getSchemas()));
	}

}

?>