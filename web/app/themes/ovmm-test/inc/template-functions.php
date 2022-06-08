<?php
/**
 * Template Functions
 * - Collection of useful template functions
 */

//== Skip to Content Links
function jb_skip_link() {
	// Default content area
	$target = '#content';

	echo '<a href="' . $target . '" id="jb-skip-link">Skip to content</a>';
}


//== Check what template is currently being used
// Return true or false if the a given template is the current
// template being used, accepts either string or array.
// Usage:
// - jb_is_template( 'department' )
// - jb_is_template( array( 'department', 'calendar' ) )
// Don't include the `.php` since all templates include that, only the
// name of the template in the /templates folder.
// Special case for news since the actual template is `archive.php` but is
// validated by checking `is_home`
function jb_is_template( $templates ) {
	$found_matching_template = false;

	// Convert to array if `$templates` isn't one
	if (!is_array($templates)) {
		$templates = array($templates);
	}

	foreach ($templates as $template) {
		if (
			($template == 'news' && (is_home() || is_search() || is_archive())) ||
			basename(get_page_template()) == $template . '.php'
		) {
			$found_matching_template = true;
		}
	}

	return $found_matching_template;
}

//== JB Get Template Part - with variables!
// Basically a hackerish way to pass variables into templates w/o setting
// each variable as a global.
// h/t: http://mekshq.com/passing-variables-via-get_template_part-wordpress/
function jb_get_template_part( $template, $variables = array() ) {
	// If the template doesn't contain `.php` add it to the end
	// of the template name
	if (strpos($template, '.php') === false) {
		$template = "{$template}.php";
	}

	// Extract out each element in the array as a
	// variable to use in the template
	if ($variables) {
		extract($variables);
	}

	// Note: you need to use full file name, in this case content.php and also
	// false arguments to avoid loading the file and return the full path instead
	include locate_template($template, false, false);
}


/*
 * Get nav menu array - convert a wp nav menu into a nested array
 * h/t: https://developer.wordpress.org/reference/functions/wp_get_nav_menu_items/#comment-1800
 */
function jb_get_nav_menu_array( $nav_menu, $args = [] ) {
	$menu      = array();
  $submenu   = array();

	// Define the array of defaults
	$defaults = array(
		'include_children' => true,
	);

	// Parse incoming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	// Get the nav menu items
  $nav_items = wp_get_nav_menu_items( $nav_menu );

  if ( empty( $nav_items ) ) {
		return $menu;
  }

  foreach ( $nav_items as $m ):
    if ( empty( $m->menu_item_parent ) ):
      $menu[$m->ID]             	= array();
      $menu[$m->ID]['ID']       	= $m->ID;
      $menu[$m->ID]['title']    	= $m->title;
      $menu[$m->ID]['url']      	= $m->url;
			$menu[$m->ID]['object_id']  = $m->object_id;
      $menu[$m->ID]['children'] 	= array();
    endif;
  endforeach;


	if ( $args['include_children'] ):
	  foreach ( $nav_items as $m ):
	    if ( $m->menu_item_parent ):
	      $submenu[$m->ID]                                = array();
	      $submenu[$m->ID]['ID']                          = $m->ID;
	      $submenu[$m->ID]['title']                       = $m->title;
	      $submenu[$m->ID]['url']                         = $m->url;
				$menu[$m->ID]['object_id']                      = $m->object_id;
	      $menu[$m->menu_item_parent]['children'][$m->ID] = $submenu[$m->ID];
	    endif;
	  endforeach;
	endif;

  return $menu;
}

// camelCase text - loveningly stolen from Laravel's text helpers
// https://github.com/rappasoft/laravel-helpers/blob/f2454f75ea582cc7b25df06b6d8076657344a190/src/helpers.php#L450
function jb_camel_case( $text ) {
	$text = str_replace( array('-', '_'), ' ', $text );
	$text = ucwords( $text );
	$text = str_replace(' ', '', $text);
	$text = lcfirst( $text );

	return $text;
}

