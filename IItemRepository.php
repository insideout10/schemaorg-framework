<?php

/**
 * This interface defines which method an Item Repository shall implement.
 */
interface IItemRepository {
	
	/**
	 * Finds all the items in the repository.
	 * @param integer $offset The offset to start from.
	 * @param integer $limit The maximum number of elements.
	 * @return array An array of items.
	 */
	public function findAll($offset = 0, $limit = -1);
	
	/**
	 * Finds all the items in the repository given their schema name. 
	 * @param string $schema The type to search for.
	 * @param integer $offset The offset to start from.
	 * @param integer $limit The maximum number of elements.
	 * @return array An array of items of the specified type.
	 */
	public function findBySchema($schema, $offset = 0, $limit = -1);
	
	/**
	 * Finds all the items in the repository given their schema name and the name alike.
	 * @param string $schema The type to search for.
	 * @param string $name The name that must be contained.
	 * @param integer $offset The offset to start from.
	 * @param integer $limit The maximum number of elements.
	 * @return array An array of items of the specified type.
	 */
	public function findBySchemaAndNameLike($schema, $name, $offset = 0, $limit = -1);
	
	/**
	 * Finds the item with the corresponding url. 
	 * @param string $url The URL of the item to find.
	 * @return mixed|null An item or null.
	 */
	public function findByUrl($url);
}

?>