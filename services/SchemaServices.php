<?php

/**
 * @requires WordPressFramework
 * @requires WordLiftPlugin
 */
class SchemaServices {

	private $logger;

	function __construct() {
		$this->logger 		= Logger::getLogger(__CLASS__);
	}

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
        array_walk_recursive($items, create_function(
                '&$array',
                '$type = get_class($array);' .
        	    '$array = array_filter((array)$array);' .
        	    '$array[\'@type\'] = $type;'
        ));
        
        return $items;
    }
    
    /**
     * @service ajax
     * @action schema-org.create-post
     * @authentication none
     * @requireCapabilities publish_posts
     */
    public function createPost($requestBody = null) {
        
        $this->logger->debug("requestBody: " . $requestBody);
        
        $properties = json_decode($requestBody, true);
        $return = $this->create($properties);
        
        echo $return;
        
        return AjaxService::CALLBACK_RETURN_NULL;
        // return $instance;
    }
    
    private function create($properties) {

        if (0 < sizeof($properties) &&  is_array($properties[0])) {
            $value = array();
            foreach ($properties as $property) {
                array_push( $value, $this->create($property));
            }
            return implode(",", $value);
        }

        $post = array(
            WordPressFramework::POST_TYPE => WordLiftPlugin::POST_TYPE
        );
        
        $post_meta = array();
        
        foreach ($properties as $key => $value) {
            
            // set the schema-type.
            if (SchemaOrgFramework::TYPE === $key) {
                $post_meta[WordLiftPlugin::SCHEMA_TYPE] = $value;                
                continue;
            }

            // create dependencies.
            if (true === is_array($value)) {
                $value = $this->create($value);

                if (null !== $post_meta[WordLiftPlugin::FIELD_PREFIX . $key])
                    $post_meta[WordLiftPlugin::FIELD_PREFIX . $key] = $post_meta[WordLiftPlugin::FIELD_PREFIX . $key] . "," . $value;
                else
                    $post_meta[WordLiftPlugin::FIELD_PREFIX . $key] = $value;

                continue;
            }

            // set the name.
            if (SchemaOrgFramework::NAME === $key) {
                $post[WordPressFramework::POST_TITLE] = $value;
                // we want to save this property also in the custom fields, we don't "continue".
            }

            // set this post custom-fields.
            $post_meta[WordLiftPlugin::FIELD_PREFIX . $key] = $value;
        }
        
        if (null === $post[WordPressFramework::POST_TITLE])
            $post[WordPressFramework::POST_TITLE] = uniqid("", true);

        if (null === $post[WordPressFramework::POST_NAME])
            $post[WordPressFramework::POST_NAME] = sanitize_title($post[WordPressFramework::POST_TITLE]);
        
        $post_id = wp_insert_post($post, true);
        if ( is_wp_error($post_id) )
            echo $post_id->get_error_message();

        print_r($post);
        print_r($post_meta);
        
        // echo $post_id;

        foreach ($post_meta as $key => $value)
            update_post_meta($post_id, strtolower($key), $value);

        return $post[WordPressFramework::POST_NAME];

    }
    
}

?>