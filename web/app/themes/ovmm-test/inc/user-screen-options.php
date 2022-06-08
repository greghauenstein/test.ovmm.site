<?php
/**
 * Set Default User Screen Options
 * - Ensure some reasonable screen options defaults are set for each user
 * - Note: only runs on user register, won't affect existing users
 */

function jb_set_default_hidden_meta_boxes( $hidden, $screen ) {
	// Grab the current post type
	$post_type = $screen->post_type;

	// Array of meta boxes to make sure are unhidden
	$meta_boxes_to_display = array( 'revisionsdiv' );

	// Only update on posts or pages
	if (  $post_type == 'post' || $post_type == 'page' ):
		// If any of the meta boxes we want to display are found
		// remove them from the meta boxes to hide
		foreach ( $meta_boxes_to_display as $meta_box ):
			if ( in_array( $meta_box, $hidden ) ) {
				$hidden = array_diff( $hidden, array( $meta_box ) );
			}
		endforeach;
	endif;

	return $hidden;
}
add_action( 'default_hidden_meta_boxes', 'jb_set_default_hidden_meta_boxes', 10, 2 );
