<?php
require_once realpath(dirname(dirname(dirname(dirname(dirname($_SERVER["SCRIPT_FILENAME"])))))) . '/wordlift.php';

$schema = $_GET['schema'];
$name = $_GET['name'];
$url = $_GET['url'];

$wordPressRepository = new WordPressRepository(WORDLIFT_20_ENTITY_CUSTOM_POST_TYPE, WORDLIFT_20_FIELD_SCHEMA_TYPE, '', WORDLIFT_20_FIELD_PREFIX);

if (null != $schema && null != $name) {
	$items = $wordPressRepository->findBySchemaAndNameLike($schema, $name);
} else if (null != $schema) {
	$items = $wordPressRepository->findBySchema($schema);
} else if (null != $url) {
	$items = $wordPressRepository->findByUrl($url);
} else {
	$items = $wordPressRepository->findAll();
}

// clean out null properties
// $items = array_filter(get_object_vars($items));
array_walk_recursive($items, function(&$array) {
	$type = get_class($array);
	$array = array_filter((array)$array);
	$array['@type'] = $type;
});


/************************************************************
 * HTTP Response starts here.								*
 ************************************************************/

header('content-type: application/json; charset=utf-8');
// header("access-control-allow-origin: *");

// Turn on output buffering with the gzhandler
// http://www.geekality.net/2011/10/31/php-simple-compression-of-json-data/
ob_start('ob_gzhandler');

// create a JSONO representation of the result.
$json = json_encode($items);

# JSON if no callback
if( ! isset($_GET['callback']))
	exit($json);

# JSONP if valid callback
if(is_valid_callback($_GET['callback']))
	exit("{$_GET['callback']}($json)");

# Otherwise, bad request
header('status: 400 Bad Request', true, 400);


/**
 * Handy stuff to check that the callback is valid: see
 * http://www.geekality.net/2010/06/27/php-how-to-easily-provide-json-and-jsonp/
 */
function is_valid_callback($subject)
{
	$identifier_syntax
	= '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';

	$reserved_words = array('break', 'do', 'instanceof', 'typeof', 'case',
			'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue',
			'for', 'switch', 'while', 'debugger', 'function', 'this', 'with',
			'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum',
			'extends', 'super', 'const', 'export', 'import', 'implements', 'let',
			'private', 'public', 'yield', 'interface', 'package', 'protected',
			'static', 'null', 'true', 'false');

	return preg_match($identifier_syntax, $subject)
	&& ! in_array(mb_strtolower($subject, 'UTF-8'), $reserved_words);
}

?>