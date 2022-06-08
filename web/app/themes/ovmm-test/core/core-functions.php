<?php
/**
 * CORE FUNCTIONS
 * Defines built-in functionality that will be unchanging across all sites (user interface and utility functions)
 * Last updated: 5/24/2017
 */

/**
 * DEFAULT SETTINGS
 * Default site settings. To override, define the constants at the top of functions.php before the
 *   core functions file is called.
 */

if (!defined('JB_IMAGE_MAX_WIDTH'))       // Max width for uploaded images.
	define('JB_IMAGE_MAX_WIDTH', 1280);
if (!defined('JB_IMAGE_MAX_HEIGHT'))      // Max height for uploaded images.
	define('JB_IMAGE_MAX_HEIGHT', 1024);
if (!defined('JB_GOOGLE_SEARCH_ID'))      // Google custom site search ID.
	define('JB_GOOGLE_SEARCH_ID', false);
if (!defined('JB_LOAD_MIGRATE'))          // Disable jQuery Migrate by default
	define('JB_LOAD_MIGRATE', false);
if (!defined('JB_LOAD_FONTAWESOME'))      // Load FontAwesome for vector icons
	define('JB_LOAD_FONTAWESOME', false);
if (!defined('JB_FEED_URL'))              // Feed URL for the Juicebox blog
	define('JB_FEED_URL', 'https://juiceboxinteractive.com/feed/');
if (!defined('JB_BLOG_URL'))              // URL to the main Juicebox blog
	define('JB_BLOG_URL', 'https://juiceboxinteractive.com/blog/');
if (!defined('JB_HIDE_MENUS'))            // Whether to hide the Menus menu from the end user (non-jbadmin)
	define('JB_HIDE_MENUS', false);
if (!defined('JB_HIDE_WIDGETS'))          // Whether to hide the Widgets menu from the end user (non-jbadmin)
	define('JB_HIDE_WIDGETS', true);
if (!defined('JB_HIDE_READING_MEDIA'))    // Whether to hide Reading and Media from the end user
	define('JB_HIDE_READING_MEDIA', true);
if (!defined('JB_HIDE_PERMALINKS'))       // Whether to hide Permalinks from the end user
	define('JB_HIDE_PERMALINKS', true);
if (!defined('JB_HIDE_READ_MORE_JUMP'))   // Whether to disable the "jump" when you click a Read More link
	define('JB_HIDE_READ_MORE_JUMP', true);
if (!defined('JB_ENABLE_XMLRPC'))         // Whether to allow the XMLRPC protocol (security)
	define('JB_ENABLE_XMLRPC', false);
if (!defined('JB_EXCERPT_LENGTH'))        // Override excerpt length ('false' to leave it unchanged)
	define('JB_EXCERPT_LENGTH', 38);
if (!defined('JB_EXCERPT_MORE'))          // Override excerpt termination ('false' to leave it unchanged)
	define('JB_EXCERPT_MORE', ' ...');
if (!defined('JB_DISABLE_TAGS'))          // Hide post tags from the user interface
	define('JB_DISABLE_TAGS', true);
if (!defined('JB_ADMIN_USERNAME'))        // The 'super admin' username for whitelisting access to WP core functionality
	define('JB_ADMIN_USERNAME', 'jbadmin');
if (!defined('JB_ENABLE_EMOJI'))          // Whether or not to support emoji (new in WP 4.2)
	define('JB_ENABLE_EMOJI', false);
if (!defined('JB_TWITTER_URL'))           // Twitter URL
	define('JB_TWITTER_URL', 'http://twitter.com/juiceboxint');
if (!defined('JB_FB_URL'))                // Facebook URL
	define('JB_FB_URL', 'https://www.facebook.com/juiceboxint');
if (!defined('JB_EVENT_FEED_CRON_START_TIME')) // Deprecated, but we're keeping it around for backwards compatibility
	define('JB_EVENT_FEED_CRON_START_TIME', 'tomorrow +2 hours');
if (!defined('JB_HIDE_UPDATES'))          // Hide post tags from the user interface
	define('JB_HIDE_UPDATES', true);
if (!defined('DISALLOW_FILE_EDIT'))       // Core WP option that prevents file edits via the UI
	define('DISALLOW_FILE_EDIT', true);
if (!defined('JB_LOGIN_WITH_EMAIL'))      // Whether to allow authentication with email address in addition to username
	define('JB_LOGIN_WITH_EMAIL', false);
if (!defined('JB_STAGING_SITE_WARNING'))  // Whether to display the dev site warning based on the environment variable.
	define('JB_STAGING_SITE_WARNING', false);
if (!defined('JB_ADMIN_MEMORY_LIMIT'))    // Bump up the admin memory limit for all users. Set to 'false' to disable.
	define('JB_ADMIN_MEMORY_LIMIT', '256M');
if (!defined('JB_DISABLE_REST_API'))      // Disable the REST API out of the box to reduce surface area of attack.
	define('JB_DISABLE_REST_API', true);
if (!defined('JB_HIDE_DEFAULT_POST_CUSTOM')) // Hide default "Custom Meta" box (since we always use a custom fields plugin)
	define('JB_HIDE_DEFAULT_POST_CUSTOM', true);

/**
 * UTILITY FUNCTIONS
 */

/** Utility alias for get_post_meta with simpler syntax and saner defaults. */
function jb_get_field($key, $post_id = 0, $single = true, $prepend = true) {
	global $post;
	// Prepend meta key with '_jb_' unless it begins with a '_' already or
	// the $prepend variable is false.
	if ($prepend && $key[0] !== '_') {
		$key = '_jb_' . $key;
	}
	if ($post_id == 0) {
		if (is_object($post) && property_exists($post, 'ID')) {
			return get_post_meta($post->ID, $key, $single);
		}
	} else {
		return get_post_meta($post_id, $key, $single);
	}
}


/** Echo-version of jb_get_field. */
function jb_field($key, $post_id = 0, $single = true) {
	echo jb_get_field($key, $post_id, $single);
}


/** If the passed ID is the current tree being viewed, output some CSS to mark it as active.
 *  Used primarily in top-level navigation. */
function check_active($pid = 0, $echo = true) {
	global $post;
	$active = false;
	if (is_descendent($pid) || (is_singular() && $post->ID == $pid)) $active = true;
	if ($active) {
		if ($echo) echo ' class="active"';
		return true;
	}
	return false;
}


/** Variation of check_active to check whether we're on a blog page. */
function check_active_blog($echo = true) {
	global $post;
	$active = false;
	if (is_home() || is_singular('post') || (is_archive() && get_post_type() == 'post') || is_search()) $active = true;
	if ($active) {
		if ($echo) echo ' class="active"';
		return true;
	}
	return false;
}


/** Variation of check_active to check whether we're viewing a specific custom post type.
 *  Allows passing multiple CPTs as an array, or a single CPT as a string. */
function check_active_cpt($cpts = array('page'), $echo = true) {
	global $post;
	$active = false;
	if (!is_array($cpts)) $cpts = array($cpts);
	foreach ($cpts as $cpt) {
		if (is_singular($cpt) || is_post_type_archive($cpt)) $active = true;
	}
	if ($active) {
		if ($echo) echo ' class="active"';
		return true;
	}
	return false;
}


/** Test if current page is a descendent of a given page. Not often called directly,
 *  but used in check_active() for top level navs. */
function is_descendent($pid) {
	if (is_singular()) {
		global $wp_query;
		$ancestors = $wp_query->post->ancestors;
		if (in_array($pid, $ancestors)) {
			return true;
		} else {
			return false;
		}
	}
}


/** Return (or echo) the blog page ID */
function jb_blog_id($echo = false) {
	$blog_id = get_option('page_for_posts');
	if ($echo) echo $blog_id;
		else return $blog_id;
}

/** Return (or echo) the homepage ID */
function jb_home_id($echo = false) {
	$home_id = get_option('page_on_front');
	if ($echo) echo $home_id;
		else return $home_id;
}


/** Get the URL of the blog based on the 'page_for_posts' option. */
function jb_blog_url($echo = true) {
	$blog_page_id = jb_blog_id();
	if ($blog_page_id) {
		$blog_url = get_permalink($blog_page_id);
		if ($echo) {
			echo $blog_url;
			return true;
		} else {
			return $blog_url;
		}
	}
	return false;
}


/** Corrects a user-input URL (usually via custom field) for suitable display on the site.
 *  Would be better to sanitize or correct it on input if possible. */
function jb_url($url, $echo = true) {
	if ($url) {
		if (substr($url, 0, 1) == '/') { // Check to make sure it's not a relative link (e.g. /news/)
			$url = get_bloginfo('url') . $url;
		} elseif (substr($url, 0, 4) !== 'http') { // Ensure the URL has a valid prefix (http or https)
			$url = 'http://' . $url;
		}
		if ($echo) echo $url;
		else return $url;
	}
}


/** Corrects a user-input Twitter username or URL for suitable display on the site. */
function jb_twitter_url($url, $echo = true) {
	if ($url) {
		if (substr($url, 0, 1) == '@') { // They put in the username, so we can assume they did not include a full URL.
			$url = str_replace('@', '', $url); // Strip "@" as first letter
			$url = 'https://twitter.com/' . $url; // Prefix with URL
		} else {
			$url = str_replace('www.', '', $url); // Change www.twitter.com to twitter.com
			if (strpos($url, 'twitter.com') === false) // If it doesn't contain twitter.com, add that to the beginning
				$url = 'https://twitter.com/' . $url;
			if (substr($url, 0, 4) !== 'http')
				$url = 'https://' . $url; // If not http, add that to the beginning
		}
		if ($echo) echo $url;
		else return $url;
	}
}


/** Add breakpoints (zero-width spaces) to URLs when they are displayed in tight spaces. */
function jb_url_breakpoints($url) {
	$url = str_replace('/', '/&#8203;', $url);
	$url = str_replace('-', '-&#8203;', $url);
	$url = str_replace('@', '@&#8203;', $url);
	return $url;
}


/** Custom excerpt length for certain contexts. If you don't pass in
 *  your own string, it will default to the normal get_the_excerpt(). */
function jb_excerpt($limit = 38, $echo = false, $text = false) {

	if (!$text) {
		global $post;
		$text = get_the_excerpt($post);
	}

	$excerpt = wp_trim_words($text, $limit, '...');

	if ($echo) {
		echo $excerpt;
	} else {
		return $excerpt;
	}
}


/** Retrieve a post object by its slug. Only works on posts and pages, not custom post types. */
if (!function_exists('get_post_by_slug')) {
	function get_post_by_slug($post_name, $output = OBJECT) {
		global $wpdb;
		$post = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND (post_type = 'post' OR post_type = 'page')", $post_name));
		if ($post) return get_post($post, $output);
		return null;
	}
}


/** Get the last modified date of the passed-in file (default to /style.css)
 *  and append this to the URL to force cache refreshing. */
function jb_cachebreaker($file = '/style.css', $child = true) {
	if ($child) {
		$filepath = get_stylesheet_directory() . $file;
	} else {
		$filepath = get_template_directory() . $file;
	}
	$time = filemtime($filepath);
	if ($time) {
		echo '?ver=' . $time;
	}
}


/** Check if the current user has the specified role. The "current_user_can($role_name)"
 *  method doesn't work correctly on multisite setups.
 *    (see https://core.trac.wordpress.org/ticket/22624) */
function jb_is_role($roles, $user_id = false) {
	if (is_numeric($user_id)) {
		$user = get_userdata($user_id);
	} else {
		$user = wp_get_current_user();
	}

	if (!is_array($roles)) {
		$roles = array($roles);
	}

	if (empty($user)) return false;

	foreach ($roles as $role) {
		if (in_array($role, (array) $user->roles)) {
			return true;
		}
	}

	return false;
}


/**
 * Renders the navigation breadcrumb. For hierarchical post types, it will show the full hierarchy
 * tree with the homepage as the first item. For non-hierarchical post types, it will link to the
 * archive as the second-level page.
 *
 * The name of the homepage (root item) can be overridden as well as the hover text by passing in
 * the `home_label` and `home_title` variables.
 *
 * It also allows spoofing of particular pages if you pass in a specific `post_id`. This is useful
 * if you want this page or template to appear to be a different page, as well as on templates without
 * an inherent global post ID (e.g. archive pages and 404s).
 *
 * The `append_current` parameter will append the current post to the breadcrumb. It's only useful
 * if you supplied a `post_id` that is different from the current post - otherwise it'll show the
 * current post twice in the breadcrumb.
 *
 * @param  string|array  $args  Array or query string of arguments.
 */
function jb_breadcrumb($args = '') {

	$defaults = array(
		'class'           => 'breadcrumb', // this is the base class
		'item_class'			=> 'breadcrumb-item',
		'extra_classes'   => array(),
		'home_label'      => '<i class="far fa-home"></i><span class="sr-only">Home</span>',
		'home_title'      => 'Return to homepage',
		'post_id'         => 0,
		'append_current'  => false
	);

	$r = wp_parse_args( $args, $defaults );

	$post_id        = $r['post_id'];
	$append_current = $r['append_current'];
	$class          = $r['class'];
	$base_classes   = $class;
	$extra_classes  = $r['extra_classes'];
	$item_class     = $r['item_class'];
	$home_label     = $r['home_label'];
	$home_title     = $r['home_title'];

	if ($extra_classes && !is_array( $extra_classes )) {
		$extra_classes = array( $extra_classes );
	}

	if (is_array( $extra_classes ) && !empty( $extra_classes )) {
		$base_classes .= ' ' . implode( ' ', $extra_classes );
	}


	if (is_404()) {

		echo '<ol class="' . $base_classes . '">' . "\n";
		echo "\t" . '<li><a href="' . get_bloginfo('url') . '">' . $home_label . '</a></li>' . "\n";
		echo "\t" . '<li class="' . $item_class . ' active">Page not found</li>' . "\n";
		echo '</ol>';

	} else if (is_search()) {

		echo '<ol class="' . $base_classes . '">' . "\n";
		echo "\t" . '<li class="' . $item_class . '"><a href="' . get_bloginfo('url') . '">' . $home_label . '</a></li>' . "\n";
		$title = 'Search results for <span class="keyword">' . get_search_query() . '</span>';
		echo "\t" . '<li class="' . $item_class . ' active">' . $title . '</li>' . "\n";
		echo '</ol>';

	} else {

		global $post;
		if ( $post_id == 0 ) {
			$post_id = $post->ID;
		}

		$type  = get_post_type( $post_id );
		$ptobj = get_post_type_object( $type );

		echo '<ol class="' . $base_classes . '">' . "\n";

		if ( !is_front_page() ) {
			// If we're not currently on the front page, make that the root.
			echo "\t" . '<li class="' . $item_class . ' root"><a href="' . get_bloginfo('url') . '"';
			if ($r['home_title']) {
				echo ' title="' . $r['home_title'] . '"';
			}
			echo '>' . $home_label . '</a></li>' . "\n";
		}

		if ( $type == 'post' ) {
			// If this is a standard posts archive, return the archive link as the second level page
			// TODO: Doesn't handle archive names yet (e.g. category, author, etc.)
			echo "\t" . '<li class="' . $item_class . '"';
			$archive_id = get_option('page_for_posts');
			$title = get_the_title($archive_id);

			if (is_home() || is_post_type_archive($type)) {
				echo ' active">' . $title . '</li>' . "\n";
				echo '</ol>';
				return;
			} else {
				echo '><a href="' . get_post_type_archive_link($type) . '">' . $title . '</a></li>' . "\n";
				$archive_title = false;
				if (is_category()) {
					$archive_title = 'Articles in <span class="keyword">' . single_cat_title('', false) . '</span>';
				} elseif (is_author()) {
					$curauth = isset($_GET['author_name']) ? get_user_by('slug', $author_name) : get_userdata(intval($author));
					$archive_title = 'Articles by <span class="keyword">' . $curauth->display_name . '</span>';
				} elseif (is_date()) {
					$yr = get_query_var('year');
					$month = get_query_var('monthnum');
					$archive_title = 'Articles from <span class="keyword">' . date('F', mktime(0, 0, 0, $month, 10)) . ' ' . $yr . '</span>';
				} elseif (is_home()) {
					$archive_title = false;
				}

				// Stop if we hit one of the conditions above
				if ($archive_title) {
					echo "\t" . '<li class="' . $item_class . ' active">' . $archive_title . '</li>' . "\n";
					return;
				}

			}
		} else if ($type != 'page') {
			// If this is a custom post type, return the archive link as the second level page
			echo "\t" . '<li';
			if (is_post_type_archive($type)) {
				echo ' class="' . $item_class . ' active">' . $ptobj->label . '</li>' . "\n";
				echo '</ol>';
				return;
			} else {
				echo '><a href="' . get_post_type_archive_link($type) . '">' . $ptobj->label . '</a></li>' . "\n";
			}
		}

		if ($ptobj->hierarchical) { // if it's hierarchical, list out the tree
			$anc = array_reverse(get_post_ancestors($post_id));
			foreach ($anc as $ancestor) {
				echo "\t" . '<li class="' . $item_class . '"><a href="' . get_permalink($ancestor) . '" title="' . get_the_title($ancestor) . '">' . get_the_title($ancestor) . '</a></li>' . "\n";
			}/**/
		}

		if ($append_current) {
			echo "\t" . '<li  class="' . $item_class . '"><a href="' . get_permalink($post_id) . '" title="' . get_the_title($post_id) . '">' . get_the_title($post_id) . '</a></li>' . "\n";
			echo "\t" . '<li class="' . $item_class . ' active">' . get_the_title($post->ID) . '</li>' . "\n";
		} else {
			echo "\t" . '<li class="' . $item_class . ' active">' . get_the_title($post_id) . '</li>' . "\n";
		}

		echo '</ol>';

	}
}


/** Write strings and arrays to log file instead of outputting to screen (debug.log in /wp-content).
 *  WP_DEBUG must be set to true for this to have any effect. */
function jb_write_log($log)  {
	if (WP_DEBUG === true) {
		if (is_array($log) || is_object($log)) {
			error_log(print_r($log, true));
		} else {
			error_log($log);
		}
	}
}


/** General-purpose function to increase the PHP memory limit if it's lower than the supplied value. */
function jb_increase_memory_limit($new = '256M') {
	$current = ini_get('memory_limit');
	$current_int = trim(strtolower($current), 'm');
	$new_int = trim(strtolower($new), 'm');

	if ($new_int > $current_int) {
		return @ini_set('memory_limit', $new);
	} else {
		return $current;
	}
}


/** Set memory limit for all users in the admin. (WP core only does it for users with `manage_options`) */
function jb_admin_memory_limit() {
	jb_increase_memory_limit(JB_ADMIN_MEMORY_LIMIT);
}
if (JB_ADMIN_MEMORY_LIMIT) {
	add_action('admin_init', 'jb_admin_memory_limit');
}


/** Add favicon link to site header and admin header. */
function add_favicon_ref() {
	// Supports child themes by checking child theme directory first, then parent theme.
	if (file_exists(STYLESHEETPATH . '/favicon.ico')) {
		echo '<link rel="shortcut icon" href="' . get_stylesheet_directory_uri() . '/favicon.ico" type="image/x-icon" />' . "\n\n";
	} else if (file_exists(TEMPLATEPATH . '/favicon.ico')) {
		echo '<link rel="shortcut icon" href="' . get_template_directory_uri() . '/favicon.ico" type="image/x-icon" />' . "\n\n";
	}
}
add_action('wp_head', 'add_favicon_ref', 0);
add_action('admin_head', 'add_favicon_ref');


/** Clean up feed links in the header... we want to be in control of these. */
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);


/** Remove the "Read More" jump by regexing out the anchor. */
function jb_disable_jump($content) {
	$pattern = "/\#more-\d+\" class=\"more-link\"/";
	$replacement = "\" class=\"more-link\"";
	$content = preg_replace($pattern, $replacement, $content);
	return "$content";
}
if (JB_HIDE_READ_MORE_JUMP) {
	add_action('the_content', 'jb_disable_jump');
}


/** Clean up output of stylesheet <link> tags. */
function jb_clean_style_tag($input) {
	preg_match_all("!<link rel='stylesheet'\s?(id='[^']+')?\s+href='(.*)' type='text/css' media='(.*)' />!", $input, $matches);
	if (empty($matches[2])) {
		return $input;
	}
	// Only display media if it is meaningful
	$media = $matches[3][0] !== '' && $matches[3][0] !== 'all' ? ' media="' . $matches[3][0] . '"' : '';
	return '<link rel="stylesheet" href="' . $matches[2][0] . '"' . $media . '/>' . "\n";
}
add_filter('style_loader_tag', 'jb_clean_style_tag');


/** Clean up output of <script> tags. */
// function jb_clean_script_tag($input) {
// 	return str_replace("'", '"', $input);
// }
// add_filter('script_loader_tag', 'jb_clean_script_tag');


/** Remove meta generator from head */
add_filter('the_generator', '__return_false');


/** Remove garbage comments from WP Missed Schedule.
 * (The plugin author is COMPLETELY INSANE, but his plugin works very well.) */
remove_action('wp_head', 'wpms_shfl', 100);
remove_action('wp_footer', 'wpms_shfl', 100);


/** Yoast SEO: Lower the priority of the SEO meta box */
function jb_seo_metabox_priority() {
	return 'low';
}
add_filter('wpseo_metabox_prio', 'jb_seo_metabox_priority', 20);


/** Yoast SEO: Disable a few things that require filters. This can be disabled via functions.php
 *  by passing in the opposite boolean function with a higher priority. */
add_filter('disable_wpseo_json_ld_search', '__return_true', 5);
add_filter('wpseo_use_page_analysis', '__return_false', 5);
add_filter('wpseo_prev_rel_link', '__return_false', 5);
add_filter('wpseo_next_rel_link', '__return_false', 5);
if (class_exists('Yoast_Notification_Center')) {
	remove_action('admin_notices', array(Yoast_Notification_Center::get(), 'display_notifications'));
	remove_action('all_admin_notices', array(Yoast_Notification_Center::get(), 'display_notifications'));
}


/** Yoast SEO: Disable onpage.org tracking. */
function jb_filter_yoast_seo_options($options) {
	$options['onpage_indexability'] = false;
	$options['enable_setting_pages'] = false;
	$options['enable_admin_bar_menu'] = false;
	return $options;
}
add_filter('option_wpseo', 'jb_filter_yoast_seo_options', 5);


/** Which built-in scripts to load. jQuery is always loaded, and then others are based on settings. */
function jb_load_scripts() {
	wp_enqueue_script('jquery');
	if (JB_LOAD_FONTAWESOME) {
		wp_enqueue_style('font-awesome', get_template_directory_uri() . '/core/font-awesome/css/font-awesome.min.css', array(), null, 'all');
	}
	// Register custom build of lodash to be used by events or directory system (but do not enqueue)
	wp_register_script('lodash', get_bloginfo('stylesheet_directory') . '/scripts/lodash.custom.min.js', array(), '4.14.4', true);
}
add_action('wp_enqueue_scripts', 'jb_load_scripts', 5);


/** Disable jQuery Migrate */
function jb_no_migrate(&$scripts) {
	if (!is_admin()){
		$scripts->remove('jquery');
		$scripts->add('jquery', false, array('jquery-core'), '1.11.3');
	}
}
if (JB_LOAD_MIGRATE == false) {
	add_filter('wp_default_scripts', 'jb_no_migrate');
}


/** Turn off and disable XMLRPC. It's only needed for remote publishing, like the WP
 *  mobile app or desktop publishing platforms. */
if (JB_ENABLE_XMLRPC == false) {
	add_filter('xmlrpc_enabled', '__return_false');
	add_action('wp', function() {
		remove_action('wp_head', 'rsd_link');
	}, 9);
}

/** Change default excerpt length. (set the global to 'false' to leave it untouched) */
function jb_excerpt_length($length) {
	return JB_EXCERPT_LENGTH;
}
if (JB_EXCERPT_LENGTH) {
	add_filter('excerpt_length', 'jb_excerpt_length', 999);
}

/** Change the default [...] excerpt termination. */
function jb_excerpt_more($more) {
	return JB_EXCERPT_MORE;
}
if (JB_EXCERPT_MORE) {
	add_filter('excerpt_more', 'jb_excerpt_more');
}


/**
 * ENVIRONMENT SETUP
 */


/** Remove unwanted components from admin bar */
function jb_declutter_admin_bar() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('comments');
	$wp_admin_bar->remove_menu('customize');
	$wp_admin_bar->remove_menu('about');
	$wp_admin_bar->remove_menu('wporg');
	$wp_admin_bar->remove_menu('documentation');
	$wp_admin_bar->remove_menu('support-forums');
	$wp_admin_bar->remove_menu('feedback');
	$wp_admin_bar->remove_menu('search');
	$wp_admin_bar->remove_menu('wpseo-menu');

	// Add our URL
	$wp_admin_bar->add_node(array(
		'id'     => 'jb_url',
		'title'  => 'Juicebox Interactive',
		'href'   => 'https://juiceboxinteractive.com',
		'parent' => 'wp-logo'
	));

	// Add our blog URL
	$wp_admin_bar->add_node(array(
		'id'     => 'jb_blog',
		'title'  => 'Juicebox Blog',
		'href'   => 'https://juiceboxinteractive.com/blog/',
		'parent' => 'wp-logo'
	));

	// Add our social media links
	$wp_admin_bar->add_node(array(
		'id'     => 'jb_twitter',
		'title'  => 'Twitter',
		'href'   => JB_TWITTER_URL,
		'parent' => 'wp-logo'
	));
	$wp_admin_bar->add_node(array(
		'id'     => 'jb_fb',
		'title'  => 'Facebook',
		'href'   => JB_FB_URL,
		'parent' => 'wp-logo'
	));
}
add_action('wp_before_admin_bar_render', 'jb_declutter_admin_bar');


/** Change the WP logo to ours. */
function jb_admin_bar_logo() {
	if (is_user_logged_in()) {
	?>
	<style type="text/css">
		#wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before { content: none; }
		#wpadminbar #wp-admin-bar-wp-logo > .ab-item { background: url('<?php echo get_template_directory_uri(); ?>/src/img/jb-admin-bar-logo.png') no-repeat center top; }
		#wpadminbar #wp-admin-bar-wp-logo:hover > .ab-item { background-position: center bottom; }
	</style>
<?php }
}
add_action('admin_head', 'jb_admin_bar_logo');
add_action('wp_head', 'jb_admin_bar_logo');


/** Remove unwanted menu items from sidebar in admin. */
function jb_declutter_admin_sidebar() {
	global $menu;
	$restricted_menu = array('Links', 'Comments');
	$restricted_menu = apply_filters('jb_restricted_menu', $restricted_menu, $menu);
	end ($menu);
	while (prev($menu)) {
		$value = explode(' ', $menu[key($menu)][0]);
		if (in_array($value[0] != NULL ? $value[0] : "", $restricted_menu))
			unset($menu[key($menu)]);
	}

	global $submenu;
	$restricted_submenu = array(
		'themes.php' => array(6 => 'Customize')
	);

	// Prevent non-super user from changing themes, using the code editor, changing permalinks, etc.
	global $current_user;
	$current_user = wp_get_current_user();
	if ($current_user->user_login != JB_ADMIN_USERNAME) {
		$restricted_submenu['themes.php'][5] = 'Themes';
		$restricted_submenu['themes.php'][12] = 'Editor';
		$restricted_submenu['plugins.php'][15] = 'Editor';
		if (JB_HIDE_READING_MEDIA) {
			$restricted_submenu['options-general.php'][20] = 'Reading';
			$restricted_submenu['options-general.php'][30] = 'Media';
		}
		if (JB_HIDE_PERMALINKS) {
			$restricted_submenu['options-general.php'][40] = 'Permalinks';
		}
		if (JB_HIDE_WIDGETS) {
			$restricted_submenu['themes.php'][7] = 'Widgets';
		}
		if (JB_HIDE_MENUS) {
			$restricted_submenu['themes.php'][10] = 'Menus';
		}
		if (JB_HIDE_UPDATES) {
			$restricted_submenu['index.php'][10] = 'Updates';
		}
	}
	// Disable tags from the interface according to setting
	if (JB_DISABLE_TAGS) {
		$restricted_submenu['edit.php'][16] = 'Tags';
		remove_meta_box('tagsdiv-post_tag', 'post', 'normal');
	}

	// Use `jb_restricted_submenu` filter to add additional menu items to the restriction
	$restricted_submenu = apply_filters('jb_restricted_submenu', $restricted_submenu, $submenu);
	foreach ($restricted_submenu as $section => $titles) {
		foreach ($titles as $index => $title) {
			if (isset($submenu[$section][$index][0]) && strpos($submenu[$section][$index][0], $title) !== false)
				unset($submenu[$section][$index]);
		}
	}
}
add_action('admin_menu', 'jb_declutter_admin_sidebar', 1000);


/** Remove meta boxes from dashboard. */
function jb_declutter_admin_dashboard() {
	remove_meta_box('dashboard_primary', 'dashboard', 'side');
	remove_meta_box('dashboard_secondary', 'dashboard', 'side');
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
	remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
	remove_meta_box('dashboard_browser_nag', 'dashboard', 'normal');
	update_user_meta(get_current_user_id(), 'show_welcome_panel', false);
}
add_action('wp_dashboard_setup', 'jb_declutter_admin_dashboard');


/** (Separate hook for the network dashboard, multisite only) */
function jb_declutter_network_dashboard() {
	remove_meta_box('dashboard_primary', 'dashboard-network', 'side');
	remove_meta_box('dashboard_secondary', 'dashboard-network', 'side');
}
add_action('wp_network_dashboard_setup', 'jb_declutter_network_dashboard');


/** Remove "new feature" bubbles. */
function jb_disable_pointers() {
	remove_action('admin_enqueue_scripts', array('WP_Internal_Pointers', 'enqueue_scripts'));
}
add_action('admin_init', 'jb_disable_pointers');


/** Remove "Howdy" message in admin bar. */
function jb_well_hello_yourself($wp_admin_bar) {
	$my_account = $wp_admin_bar->get_node('my-account');
	$newtitle = str_replace('Howdy, ', '', $my_account->title);
	$wp_admin_bar->add_node(array(
		'id' => 'my-account',
		'title' => $newtitle
	));
}
add_filter('admin_bar_menu', 'jb_well_hello_yourself', 25);


/** Hide default Post Custom box since we always use custom fields plugins. */
function jb_hide_default_post_custom($post_type, $context, $post) {
	remove_meta_box('postcustom', $post_type, $context);
}
// Note: Developer's Custom Fields does this the exact same way, so we check if that function exists before doing the removal.
if (!function_exists('slt_remove_default_meta_box') && (!defined('JB_HIDE_DEFAULT_POST_CUSTOM') || JB_HIDE_DEFAULT_POST_CUSTOM == true)) {
	add_action('do_meta_boxes', 'jb_hide_default_post_custom', 1, 3);
}

/** Remove H1, H5, H6, Pre and Address from TinyMCE format dropdown. */
if (!function_exists('base_custom_mce_format')) {
	function base_custom_mce_format($init) {
		$init['block_formats'] = "Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4";
		return $init;
	}
	add_filter('tiny_mce_before_init', 'base_custom_mce_format' );
}


/** CSS layout tweaks for DCF and ACF. */
function jb_admin_css() { ?>
<style type="text/css">
	.slt-cf-label { display: inline-block; min-width: 150px; }
	.slt-cf-checkbox input[type="checkbox"] { margin-right: 4px; }
	.slt-cf-file label { margin-bottom: 6px !important; }
	.postbox .inside img { max-width: 100%; height: auto; }
	.small-wysiwyg.acf-field-wysiwyg iframe,
	.small-wysiwyg.acf-field-wysiwyg .wp-editor-area {
		min-height: 70px;
		height: 70px !important;
	}
	.medium-wysiwyg.acf-field-wysiwyg iframe,
	.medium-wysiwyg.acf-field-wysiwyg .wp-editor-area {
		min-height: 200px;
		height: 200px !important;
	}
	.auto-iframe .acf-oembed .canvas iframe {
		width: auto;
	}
	.jb-button .dashicons {
		line-height: 26px;
		font-size: 16px;
		width: auto;
		margin-left: -3px;
	}
</style>
<?php }
add_action('admin_head', 'jb_admin_css');

/** Remove unnecessary columns from different post types. */
function jb_remove_pages_columns($defaults) {
	unset($defaults['comments']);
	return $defaults;
}
function jb_remove_posts_columns($defaults) {
	unset($defaults['comments']);
	unset($defaults['tags']);
	return $defaults;
}
function jb_remove_media_columns($defaults) {
	unset($defaults['comments']);
	return $defaults;
}
add_filter('manage_posts_columns', 'jb_remove_posts_columns');
add_filter('manage_pages_columns', 'jb_remove_pages_columns');
add_filter('manage_media_columns', 'jb_remove_media_columns');


/** Set a max width and height for uploaded images. Prevents users from uploading full-resolution camera images.
 *  Width and height are set in functions.php via defines (1280x1024 is the default).
 *  Set these to 'false' to disable the resizing entirely.
 *  On the roadmap for the future is to offload the image resizing to a service like TinyJPG. */
function jb_upload_resize($array){
	if ($array['type'] == 'image/jpeg' OR $array['type'] == 'image/gif' OR $array['type'] == 'image/png') {
		require_once('class.resize.php');
		$maxwidth = JB_IMAGE_MAX_WIDTH;
		$maxheight = JB_IMAGE_MAX_HEIGHT;
		$imagesize = getimagesize($array['file']); // $imagesize[0] = width, $imagesize[1] = height

		if ($maxwidth == 0 OR $maxheight == 0) {
			if ($maxwidth == 0) {
				$objResize = new RVJ_ImageResize($array['file'], $array['file'], 'H', $maxheight);
			}
			if ($maxheight == 0) {
				$objResize = new RVJ_ImageResize($array['file'], $array['file'], 'W', $maxwidth);
			}
		} else {
			if (($imagesize[0] >= $imagesize[1]) AND ($maxwidth * $imagesize[1] / $imagesize[0] <= $maxheight))  {
				$objResize = new RVJ_ImageResize($array['file'], $array['file'], 'W', $maxwidth);
			} else {
				$objResize = new RVJ_ImageResize($array['file'], $array['file'], 'H', $maxheight);
			}
		}
	}
	return $array;
}
if (JB_IMAGE_MAX_WIDTH && JB_IMAGE_MAX_HEIGHT) {
	add_action('wp_handle_upload', 'jb_upload_resize');
}

/** Change admin footer text. */
function jb_footer_message($text) {
	$orig = '<span id="footer-thankyou">Thank you for creating with <a href="https://wordpress.org/">WordPress</a>.</span>';
	$jb = '<a href="http://juiceboxinteractive.com" target="_blank">Juicebox Interactive</a>';
	echo str_replace($orig, $jb, $text);
}
add_filter('admin_footer_text', 'jb_footer_message');

/** Remove Help from admin interface */
function jb_remove_contextual_help() {
	$screen = get_current_screen();
	if (is_object($screen)) {
		$screen->remove_help_tabs();
	}
}
add_action('admin_head', 'jb_remove_contextual_help');

/** Remove emoji support. */
function jb_disable_emoji() {
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	add_filter('emoji_svg_url', '__return_false');
	add_filter('tiny_mce_plugins', 'jb_disable_emoji_tinymce');
}

function jb_disable_emoji_tinymce($plugins) {
	if (is_array($plugins)) {
		return array_diff($plugins, array('wpemoji'));
	} else {
		return array();
	}
}

if (JB_ENABLE_EMOJI == false) {
	add_action('init', 'jb_disable_emoji');
}


/** Security: Prevent author ID scans to detect usernames. */
function jb_disable_author_scans($query_vars) {
	if (!is_admin() &&
		!empty($query_vars['author']) && is_numeric(preg_replace('/[^0-9]/', '', $query_vars['author'])) &&
		(
			(isset($_GET['author']) && is_numeric(preg_replace('/[^0-9]/', '', $_GET['author']))) ||
			(isset($_POST['author']) && is_numeric(preg_replace('/[^0-9]/', '', $_POST['author'])))
		)
	) {
		$query_vars['author'] = -1;
	}
	return $query_vars;
}
add_action('request', 'jb_disable_author_scans');


/** Disable REST API (wp-json). */
function jb_disable_rest_api() {
	// Filters for WP-API version 1.x
	add_filter('json_enabled', '__return_false');
	add_filter('json_jsonp_enabled', '__return_false');

	// Filters for WP-API version 2.x
	add_filter('rest_enabled', '__return_false');
	add_filter('rest_jsonp_enabled', '__return_false');

	// Remove REST API info from head and headers
	remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');
	remove_action('wp_head', 'rest_output_link_wp_head', 10);
	remove_action('template_redirect', 'rest_output_link_header', 11);

	// Remove oEmbed discovery scripts from head
	remove_action('wp_head', 'wp_oembed_add_discovery_links');
	remove_action('wp_head', 'wp_oembed_add_host_js');
}
if (JB_DISABLE_REST_API) {
	add_action('init', 'jb_disable_rest_api', 1);
}

/** CPT walker to call when using wp_list_pages() with a custom post type.
 *  Adds current_page CSS classes correctly even on non-pages.
 *    Usage: 'walker' => new Walker_CPT(), */
class Walker_CPT extends Walker_Page {
	function start_el(&$output, $page, $depth = 0, $args = array(), $current_page = 0) {
		global $post;
		if (is_singular()) $current_page = $post->ID;
		// Run the original start_el function after modifying the $current_page variable
		parent::start_el($output, $page, $depth, $args, $current_page);
	}
}

/** Add necessary capabilities to administrator role when registering a custom post type.
 *  Note that each permission level also requires some level of configuration in the CPT
 *  registration function; you can't just pass in the variable arbitrarily.
 *  See `examples/type-example.php` for more information. */
function jb_cpt_caps($cpt, $level = 'intermediate') {

	// Only run this inside the admin for performance reasons
	if (!is_admin()) {
		return false;
	}

	$cpt_obj = get_post_type_object($cpt);
	$role = get_role('administrator');

	if ($level == 'basic') { // use only meta caps
		$role->add_cap('read_' . $cpt);
		$role->add_cap('edit_' . $cpt);
		$role->add_cap('delete_' . $cpt);
	} else if ($level == 'intermediate') { // use standard 7 capabilities like posts and pages
		$role->add_cap('edit_' . $cpt);
		$role->add_cap('read_' . $cpt);
		$role->add_cap('delete_' . $cpt);
		$role->add_cap('edit_' . $cpt . 's');
		$role->add_cap('edit_others_' . $cpt . 's');
		$role->add_cap('publish_' . $cpt . 's');
		$role->add_cap('read_private_' . $cpt . 's');
	} else if ($level == 'advanced') { // map_meta_cap is true; use all primitive caps
		$role->add_cap('edit_' . $cpt);
		$role->add_cap('read_' . $cpt);
		$role->add_cap('delete_' . $cpt);
		$role->add_cap('edit_' . $cpt . 's');
		$role->add_cap('edit_others_' . $cpt . 's');
		$role->add_cap('edit_published_' . $cpt . 's');
		$role->add_cap('edit_private_' . $cpt . 's');
		$role->add_cap('publish_' . $cpt . 's');
		$role->add_cap('read_' . $cpt . 's');
		$role->add_cap('read_private_' . $cpt . 's');
		$role->add_cap('delete_' . $cpt . 's');
		$role->add_cap('delete_private_' . $cpt . 's');
		$role->add_cap('delete_published_' . $cpt . 's');
		$role->add_cap('delete_others_' . $cpt . 's');
	}
}

/** Remove "Administrator" from the list of selectable roles if the current user is not an admin. */
function jb_editable_roles($roles) {
	if (isset($roles['administrator']) && !current_user_can('administrator')) {
		unset($roles['administrator']);
	}
	return $roles;
}
add_filter('editable_roles', 'jb_editable_roles');


/** Prevent editing or deleting admin accounts by non-admins. */
function jb_user_meta_cap($caps, $cap, $user_id, $args) {
	switch($cap){
		case 'edit_user':
		case 'remove_user':
		case 'promote_user':
			if (isset($args[0]) && $args[0] == $user_id)
				break;
			elseif (!isset($args[0]))
				$caps[] = 'do_not_allow';
			$other = new WP_User(absint($args[0]));
			if ($other->has_cap('administrator')) {
				if (!current_user_can('administrator')) {
					$caps[] = 'do_not_allow';
				}
			}
			break;
		case 'delete_user':
		case 'delete_users':
			if (!isset($args[0]))
				break;
			$other = new WP_User(absint($args[0]));
			if ($other->has_cap('administrator')) {
				if (!current_user_can('administrator')) {
					$caps[] = 'do_not_allow';
				}
			}
			break;
		default:
			break;
	}
	return $caps;
}
add_filter('map_meta_cap', 'jb_user_meta_cap', 10, 4);


/**
 * Admin notices for framework dependencies. Depending on which one you use, you can trigger this
 * message if a custom fields framework is not installed.
 * This should be called inside a CPT definition file. See `examples/type-example.php` for
 * example usage.
 */

/** Advanced Custom Fields: https://www.advancedcustomfields.com/pro/ */
function acf_check_notice($version = '1.0') {
?>
	<div class="error">
	<p>Advanced Custom Fields Pro version <?php echo $version; ?> or higher is required for this site to function properly.
		<a href="https://www.advancedcustomfields.com/pro/" target="_blank">Install</a>
		or <a href="<?php echo admin_url('plugins.php'); ?>">activate</a> plugin to remove this notice.</p>
	</div>
<?php
}

/** Developer's Custom Fields: https://wordpress.org/plugins/developers-custom-fields/ */
function dcf_check_notice($version = '1.0') {
?>
	<div class="error">
	<p>Developer's Custom Fields version <?php echo $version; ?> or higher is required for this site to function properly.
		<a href="<?php echo admin_url('plugin-install.php?tab=plugin-information&plugin=developers-custom-fields&TB_iframe=true&width=640&height=517'); ?>" class="thickbox onclick">Install</a>
		or <a href="<?php echo admin_url('plugins.php'); ?>">activate</a> plugin to remove this notice.</p>
	</div>
<?php
}

/** CMB2: https://github.com/WebDevStudios/CMB2 */
function cmb2_check_notice($version = '2.2') {
?>
	<div class="error">
	<p>CMB2 version <?php echo $version; ?> or higher is required for this site to function properly.
		<a href="https://github.com/WebDevStudios/CMB2" target="_blank">Include CMB2 in the theme</a> to remove this notice.</p>
	</div>
<?php
}


/** Allows for the creation of date-based archives for custom post types. (e.g. /news/2016/12/)
 *  This is called from the CPT registration when needed. */
function jb_generate_date_archives($cpt, $wp_rewrite) {
	$rules = array();

	$post_type = get_post_type_object($cpt);
	$slug_archive = $post_type->has_archive;
	if ($slug_archive === false) return $rules;
	if ($slug_archive === true) $slug_archive = $post_type->name;

	$dates = array(
		array(
			'rule' => "([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})",
			'vars' => array('year', 'monthnum', 'day')),
		array(
			'rule' => "([0-9]{4})/([0-9]{1,2})",
			'vars' => array('year', 'monthnum')),
		array(
			'rule' => "([0-9]{4})",
			'vars' => array('year'))
	);

	foreach ($dates as $data) {
		$query = 'index.php?post_type=' . $cpt;
		$rule = $slug_archive . '/' . $data['rule'];

		$i = 1;
		foreach ($data['vars'] as $var) {
			$query .= '&' . $var . '=' . $wp_rewrite->preg_index($i);
			$i++;
		}

		$rules[$rule . "/?$"] = $query;
		$rules[$rule . "/feed/(feed|rdf|rss|rss2|atom)/?$"] = $query . "&feed=" . $wp_rewrite->preg_index($i);
		$rules[$rule . "/(feed|rdf|rss|rss2|atom)/?$"] = $query . "&feed=" . $wp_rewrite->preg_index($i);
		$rules[$rule . "/page/([0-9]{1,})/?$"] = $query . "&paged=" . $wp_rewrite->preg_index($i);
	}

	return $rules;
}


/** Add the Juicebox Interactive blog feed to the dashboard. */
function jb_register_rss_metabox() {
	// Set the `JB_FEED_URL` to 'false' to disable the meta box entirely (normally it will be a URL)
	if (JB_FEED_URL == false) {
		return false;
	}

	global $wp_meta_boxes;
	wp_add_dashboard_widget('jb_blog', 'Latest News from Juicebox Interactive', 'jb_rss_metabox');

	if (isset($wp_meta_boxes['dashboard'])) {
		// Move widget to the side
		$temp_widget = $wp_meta_boxes['dashboard']['normal']['core']['jb_blog'];
		unset($wp_meta_boxes['dashboard']['normal']['core']['jb_blog']);
		$wp_meta_boxes['dashboard']['side']['core']['jb_blog'] = $temp_widget;
	}
}
add_action('wp_dashboard_setup', 'jb_register_rss_metabox');
add_action('wp_network_dashboard_setup', 'jb_register_rss_metabox');

function jb_rss_metabox() {

	// Bump up the default memory limit
	//   (it runs under the front-facing limit rather than the higher admin limit)
	$default_limit = jb_increase_memory_limit('64M');

	include_once(ABSPATH . WPINC . '/feed.php');

	// Get a SimplePie feed object from the specified feed source.
	$rss = fetch_feed(JB_FEED_URL);
	if (!is_wp_error($rss)) {
		$maxitems = $rss->get_item_quantity(4);
		$rss_items = $rss->get_items(0, $maxitems);
		$rss_title = '<a href="' . $rss->get_permalink() . '" target="_blank">' . $rss->get_title() . '</a>';
	}

	// Display the container
	echo '<div class="rss-widget">';
	echo '<ul>';

	// Check items
	if ($maxitems == 0) {
		echo '<li>Could not load latest articles.</li>';
	} else {
		// Loop through each feed item and display each item as a hyperlink.
		foreach ($rss_items as $item) {
			// Get human date (comment if you want to use non human date)
			$item_date = human_time_diff($item->get_date('U'), current_time('timestamp')) . ' ago';
			echo '<li>';
			echo '<a href="' . esc_url($item->get_permalink()) . '" title="' . $item_date . '" target="_blank">';
			echo esc_html($item->get_title());
			echo '</a>';
			echo ' <span class="rss-date">' . $item_date . '</span><br />';
			$content = $item->get_content();
			$content = wp_html_excerpt($content, 120) . ' [...]';
			echo $content;
			echo '</li>';
		}
	}
	echo '</ul>';
	echo '<a href="' . JB_BLOG_URL . '" target="_blank"><strong>More from Juicebox Interactive &raquo;</strong></a>';
	echo '</div>';

	// Restore memory limit to the default value
	@ini_set('memory_limit', $default_limit);
}


/**
 * Display admin nag when launching a site.
 * (The setting to display the nag is enabled during deployment.)
 */
/** 1. Output the markup for the launch notice. */
function jb_launch_notice() {
	$output  = '<div id="jb-launch-notice" class="error">';
	$output .= '<p>Site launch is nearly complete. Please make sure these last few tasks have been completed before <a id="dismiss-jb-launch-notice" href="javascript:;">dismissing this notice</a>:</p>';
	$output .= '<ul style="list-style-type: disc; padding-left: 20px;">';
	$output .= '<li><a href="' . admin_url('options-general.php') . '">Change admin email</a></li>';
	$output .= '<li><a href="' . admin_url('options-permalink.php') . '">Refresh permalinks</a></li>';
	$output .= '<li>Test data deleted</li>';
	$output .= '<li><a href="' . admin_url('users.php') . '">Test users removed</a></li>';
	$output .= '<li><a href="' . admin_url('admin.php?page=gf_entries') . '">Gravity Forms test entries removed</a></li>';
	$output .= '<li><a href="' . admin_url('tools.php?page=ajax-thumbnail-rebuild') . '">Thumbnails rebuilt</a></li>';
	$output .= '<li><a href="' . admin_url('options-reading.php') . '">Google Analytics code installed</a></li>';
	$output .= '<li><a href="' . get_bloginfo('url') . '/gimme-a-404">404 page</a> in place</li>';
	$output .= '<li><a href="' . admin_url('options-general.php?page=wp-mail-smtp%2Fwp_mail_smtp.php') . '">SendGrid</a> test email sent</li>';
	$output .= '<li>JB_STAGING_SITE_WARNING constant set to true in functions.php</li>';
	$output .= '</ul>';
	$output .= '<span id="jb-launch-notice-nonce" class="hidden">' . wp_create_nonce('jb-launch-notice-nonce') . '</span>';
	$output .= '</div>';
	echo $output;
}

/** 2. Javascript for AJAX request */
function jb_launch_notice_js() { ?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		if (jQuery('#dismiss-jb-launch-notice').length > 0) {
			jQuery('#dismiss-jb-launch-notice').click(function(evt) {
				evt.preventDefault();
				jQuery.post(ajaxurl, {
					action: 'jb_hide_launch_notice',
					nonce: jQuery.trim(jQuery('#jb-launch-notice-nonce').text())
				}, function (response) {
					if ('1' === response) {
						jQuery('#jb-launch-notice').fadeOut('slow');
					}
				});
			});
		}
	});
</script>
<?php
}

/** 3. JavaScript callback */
function jb_hide_launch_notice() {
	if (wp_verify_nonce($_REQUEST['nonce'], 'jb-launch-notice-nonce')) {
		if (delete_option('jb_launch_notice')) {
			die('1');
		} else {
			die('0');
		}
	}
}
add_action('wp_ajax_jb_hide_launch_notice', 'jb_hide_launch_notice');

/** 4. Load the notices only if the setting is true */
if (get_option('jb_launch_notice') == 1) {
	add_action('admin_head', 'jb_launch_notice_js');
	add_action('admin_notices', 'jb_launch_notice');
}


/** Create the "themedir" and "ajaxurl" JavaScript variables as needed. */
function site_js_vars() { ?>
<script type="text/javascript">
	var themedir = "<?php echo esc_url(str_replace('http://', '//', get_template_directory_uri())); ?>",<?php
	if (is_child_theme()) { ?>

		childdir = "<?php echo esc_url(str_replace('http://', '//', get_stylesheet_directory_uri())); ?>",<?php
	} ?>

		ajaxurl  = "<?php echo esc_url(str_replace('http://', '//', admin_url('admin-ajax.php'))); ?>";
</script>
<?php }
function admin_js_vars() { ?>
<script type="text/javascript">
	var themedir = "<?php echo esc_url(get_template_directory_uri()); ?>";<?php
	if (is_child_theme()) { ?>

	var childdir = "<?php echo esc_url(get_stylesheet_directory_uri()); ?>";<?php
	} ?>

</script>
<?php }
add_action('wp_head', 'site_js_vars', 0);
add_action('admin_head', 'admin_js_vars');


/**
 * Remove an object filter (e.g. one added by a plugin)
 * Explanation and usage: http://wordpress.stackexchange.com/a/57088
 *
 * @param  string $tag    Hook name
 * @param  string $class  Class name
 * @param  string $method Method name
 * @return void
 */
function jb_remove_object_filter($tag, $class, $method) {
	$filters = $GLOBALS['wp_filter'][$tag];

	if (empty($filters)) {
		return;
	}

	foreach ($filters as $priority => $filter) {
		foreach ($filter as $identifier => $function) {
			if (is_array($function)
				&& is_a($function['function'][0], $class)
				&& $method === $function['function'][1]) {
				remove_filter($tag,	array($function['function'][0], $method), $priority);
			}
		}
	}
}

/** Remove Query Monitor's frontend "ajaxurl" variable.
 *  We already do this on our own, and ours is better. */
function jb_remove_query_monitor_ajaxurl() {
	jb_remove_object_filter(
		'wp_head',
		'Debug_Bar',
		'ensure_ajaxurl'
	);
}
add_action('wp_head', 'jb_remove_query_monitor_ajaxurl', 0);


/** Add classes to "Next" and "Previous" links for different styling. */
function jb_prev_class() {
	return ' class="prev-posts"';
}
function jb_next_class() {
	return ' class="next-posts"';
}
add_filter('previous_posts_link_attributes', 'jb_prev_class');
add_filter('next_posts_link_attributes', 'jb_next_class');


/** Register valid query vars. The 'jbdebug' var can now be utilized
 *  throughout the code to display debugging output. */
function core_queryvars($qvars) {
	$qvars[] = 'jbdebug';
	return $qvars;
}
add_filter('query_vars', 'core_queryvars');

/** Default galleries to link to 'file' rather than the attachment page.
 *  (This isn't usually very effective, but it doesn't matter as much when
 *  we use the Jetpack gallery plugin for the Tiled Mosaic format.) */
function jb_gallery_link($out, $pairs, $atts) {
	if (!isset($atts['link'])) {
		$out['link'] = 'file';
	}
	return $out;
}
add_filter('shortcode_atts_gallery', 'jb_gallery_link', 20, 3);


/** Show a warning if we are editing the staging site after launch. */
function jb_staging_site_warning() {
	$test = apply_filters('jb_staging_site_condition', false);
	if ($test === true) {
		$url = apply_filters('jb_staging_site_warning_url', false);
		$login_text = 'log into the live website';
		if ($url) {
			$login_text = '<a href="' . jb_url($url, false) . '" target="_blank">' . $login_text . '</a>';
		}
		$message = '<p><strong>WARNING:</strong> You are currently editing the staging site. After the site has launched, <strong>any changes made here will not transfer to the new site</strong>. Please ' . $login_text . ' if you need to make changes.</p>'; ?>
		<div class="error"><?php
			echo apply_filters('jb_staging_site_warning_message', $message); ?>
		</div><?php
	}
}
if (JB_STAGING_SITE_WARNING == true) {
	add_action('admin_notices', 'jb_staging_site_warning');
}


/** Hide update notices from the client */
if (JB_HIDE_UPDATES == true) {
	add_filter('auto_core_update_send_email', '__return_false'); // Disable update notice emails
	add_action('admin_head', 'jb_disable_update_nag', 1);        // Hide update notice in header
	add_action('admin_head', 'jb_admin_updates');                // Hide update badges in sidebar
}

function jb_disable_update_nag() {
	global $current_user;
	$current_user = wp_get_current_user();
	if ($current_user->user_login != 'jbadmin') {
		remove_action('admin_notices', 'update_nag', 3);
	}
}

function jb_admin_updates() { ?>
	<style type="text/css">
		#adminmenu .update-plugins { display: none; }
	</style><?php
}


/** Useful for multisite: Add a Site ID column to the Network Admin > Sites page
 *  (sourced from WPEngine's core functions) */
if (is_multisite()) {
	add_filter('wpmu_blogs_columns', 'jb_ms_site_id');
	function jb_ms_site_id($columns) {
		$columns['site_id'] = 'ID';
		return $columns;
	}

	add_action('manage_sites_custom_column', 'jb_ms_site_id_columns', 10, 3);
	add_action('manage_blogs_custom_column', 'jb_ms_site_id_columns', 10, 3);
	function jb_ms_site_id_columns($column, $blog_id) {
		if ($column == 'site_id') {
			echo $blog_id;
		}
	}
}


/** Prevent weird problems with logging in due to object caching
 *  example: password has been changed, but Object Cache still holds old password, and therefore
 *  prevents login (sourced from WPEngine's core functions) */
if (defined('WP_CACHE') && WP_CACHE) {
	add_filter('wp_authenticate_user', 'jb_refresh_user');
	function jb_refresh_user($user) {
		wp_cache_delete($user->user_login, 'userlogins');
		return get_user_by('login', $user->user_login);
	}
}


/**
 * Tweaks to uploaded images to improve the metadata and ensure that alt
 * tags are included.
 *
 * @param  int|string  $id  The post ID of the attachment.
 */
function jb_enhance_image_meta($id) {

	// Get the auto-generated title
	$uploaded_post_id = get_post($id);
	$title = $uploaded_post_id->post_title;

	// Replace some common filename characters with spaces
	$char_array = array('-', '_', '~', '+');
	$title = str_replace($char_array, ' ', $title);

	// Trim multiple spaces between words
	$title = preg_replace("/\s+/", ' ', $title);

	// Make sure the first character is uppercase
	$title = ucfirst($title);

	// Update the original title
	$att = array(
		'ID' => $id,
		'post_title' => $title
	);
	wp_update_post($att);

	// Check and see if we have alt text already
	$alt = get_post_meta($id, '_wp_attachment_image_alt', true);
	if (!$alt) {
		// If not, copy the title into the alt text to ensure we have at least something
		update_post_meta($id, '_wp_attachment_image_alt', $title);
	}

}
add_action('add_attachment', 'jb_enhance_image_meta');
