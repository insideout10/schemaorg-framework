<?php

/**
 * A video file.
 * @schema http://schema.org/VideoObject
 */
class VideoObject extends MediaObject {
	
	/**
	 * Get the friendly name for this schema.
	 * @return string The friendly name for this schema.
	 */
	public static function getFriendlyName() {
		return 'Video Object';
	}
	
}

?>