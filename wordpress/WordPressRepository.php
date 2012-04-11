<?php

/**
 * This class implements a repository based on WordPress.
 */
class WordPressRepository implements IItemRepository {

	// the custom post type of WordPress that holds items.
	private $wpPostType;
	// the name of the custom field that holds the schema name.
	private $schemaNameField;
	// the prefix for the schema name (http://schema.org)
	private $schemaNamePrefix;
	// the prefix for custom fields.
	private $wpCustomFieldPrefix;
	
	/**
	 * Creates an instance of a new WordPressRepository.
	 * @param string $wpPostType The WordPress custom post type that identifies items.
	 * @param string $schemaNameField The name of the custom field that holds the item schema name.
	 * @param string $schemaNamePrefix The prefix before the schema name.
	 * @param string $wpCustomFieldPrefix The prefix for the custom fields that hold data for the item.
	 */
	function __construct($wpPostType, $schemaNameField, $schemaNamePrefix = 'http://schema.org/', $wpCustomFieldPrefix = '') {
		$this->wpPostType = $wpPostType;
		$this->schemaNameField = $schemaNameField;
		$this->schemaNamePrefix = $schemaNamePrefix;
		$this->wpCustomFieldPrefix = $wpCustomFieldPrefix;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see IItemRepository::findAll()
	 */
	public function findAll($offset = 0, $limit = -1) {
		$args = $this->getInitialArgs($offset, $limit);
		
		return $this->getItems($args);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see IItemRepository::findByType()
	 */
	public function findBySchema($schema, $offset = 0, $limit = -1) {
		
		$schema = $this->schemaNamePrefix . $schema;
		
		$args = array_merge(
					$this->getInitialArgs($offset, $limit),
					array(
						'meta_key' => $this->schemaNameField,
						'meta_value' => $schema,
					)
				);
		
		return $this->getItems($args);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see IItemRepository::findBySchemaAndNameLike()
	 */
	public function findBySchemaAndNameLike($schema, $name, $offset = 0, $limit = -1) {
		$schema = $this->schemaNamePrefix . $schema;
		$nameField = $this->wpCustomFieldPrefix . 'name';
		
		$args = array_merge(
					$this->getInitialArgs($offset, $limit),
					array(
						'meta_query' => array(
									'relation' => 'AND',
									array(
										'key' => $this->schemaNameField,
										'value' => $schema,
										'compare' => '='
									),
									array(
										'key' => $nameField,
										'value' => $name,
										'compare' => 'LIKE'
									)
								  )
					)
				);
		
		return $this->getItems($args);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see IItemRepository::findByUrl()
	 */
	public function findByUrl($url) {
		
		$urlField = $this->wpCustomFieldPrefix . 'url'; 
		
		$args = array_merge(
					$this->getInitialArgs(0, 1),
					array(
						'meta_key' => $urlField,
						'meta_value' => $url,
					)
				);
		
		$items = $this->getItems($args);
		
		return $items;
	}
	
	/**
	 * Creates the initial args array for the WordPress get_posts call.
	 * @param integer $offset The offset.
	 * @param integer $limit The maximum number of results.
	 * @return array An array of parameters suitable for the WordPress get_posts function. 
	 */
	private function getInitialArgs($offset = 0, $limit = -1) {
		return array(
				'numberposts' => $limit,
				'offset' => $offset,
				'post_type' => $this->wpPostType,
				'post_status' => 'any'
		);
	}
	
	/**
	 * Loads the WordPress posts given the args.
	 * @param array $args An array of arguments for the WordPress get_posts function.
	 * @return An array of WordPress posts.
	 */
	private function getItems($args) {
		$posts = get_posts($args);
		$items = $this->getItemsFromPosts($posts);
		
		return $items;
	}
	
	/**
	 * Converts the WordPress posts to items.
	 * @param array $posts
	 * @return array An array of items.
	 */
	private function getItemsFromPosts(&$posts) {
		$items = array();
		
		foreach ($posts as $post) {
			$items[] = $this->getItemFromPost($post);
		}
		
		return $items;
	}
	
	/**
	 * Converts a single WordPress post to an item.
	 * @param mixed $post A WordPress post.
	 * @return mixed An item. 
	 */
	private function getItemFromPost(&$post) {
		
		$custom = get_post_custom($post->ID);
		$schema = $custom[$this->schemaNameField][0];
		
		// if the schema is not supported return null.
		if (false == SchemaOrgFramework::isSchemaSupported($schema)) {
			return null;
		}
		
		$item = new $schema();
		$reflect = new ReflectionClass($item);
		$properties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
		
		foreach ($properties as $property) {
			$values = $custom[$this->wpCustomFieldPrefix . $property->getName()];
			
			if (0 == count($values)) {
				continue;
			}
			
			if (1 == count($values)) {
				$property->setValue($item, $values[0]);
				continue;
			}
			
			$property->setValue($item, $values);
		}
		
		
		return $item;
	}
	
}

?>