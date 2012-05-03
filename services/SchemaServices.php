<?php

class SchemaServices {

    /**
     * @service ajax
     * @action schema-org.posts
     * @authentication none
     */
    public function getPosts($schema = null, $name = null, $url = null) {

        $wordPressRepository = new WordPressRepository( WordLiftPlugin::POST_TYPE, WordLiftPlugin::FIELD_PREFIX . "schema-type", '', WordLiftPlugin::FIELD_PREFIX);

        if (null !== $schema && null !== $name) {
        	$items = $wordPressRepository->findBySchemaAndNameLike($schema, $name);
        } else if (null !== $schema) {
        	$items = $wordPressRepository->findBySchema($schema);
        } else if (null !== $url) {
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
        
        return $items;
    }
    
}

?>