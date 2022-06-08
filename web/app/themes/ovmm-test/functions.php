<?php
//== Site settings
define( 'JB_SITE_URL',	get_bloginfo('url') );
define( 'JB_SITE_NAME',	get_bloginfo('name') );
define( 'JB_RSS_URL',		get_bloginfo('rss2_url') );


//== Theme Locations
define( 'JB_THEME_DIR',			get_template_directory() );
define( 'JB_THEME_URL',			get_template_directory_uri() );
define( 'JB_DIST_URL',			JB_THEME_URL . '/dist' );
define( 'JB_DIST_DIR',			JB_THEME_DIR . '/dist' );
define( 'JB_IMG_URL',				JB_DIST_URL . '/img' );
define( 'JB_SCRIPTS_URL',		JB_DIST_URL . '/scripts' );
define( 'JB_STYLES_URL',		JB_DIST_URL . '/styles' );
define( 'JB_IMG_DIR',				JB_DIST_DIR . '/img' );
define( 'JB_SCRIPTS_DIR',		JB_DIST_DIR . '/scripts' );
define( 'JB_STYLES_DIR',		JB_DIST_DIR . '/styles' );


//== Pages
define( 'JB_FRONT_PAGE_ID', 2 );
define( 'JB_NEWS_PAGE_ID',	get_option( 'page_for_posts' ) );

//== Core Locations
// Define the locations for JB core plugins since I like to
// customize things and generally make life more difficult
define( 'JB_CORE_DIR', JB_THEME_DIR . '/core' );
define( 'JB_CORE_URL', JB_THEME_URL . '/core' );


//== Admin Roles - DRY to referece the WP roles that are administrators
define( 'ADMIN_ROLES', [ 'administrator', 'siteadmin' ] );


//== Custom Post Type Definitions
define( 'CPT_NEWS', 'post' );


/**
 * File Includer 5000 (tm)
 *
 * Additionally add directories as needed
 */
$included_directories = [
	'core',
	'app',
	'inc',
];

foreach ( $included_directories as $directory ):
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( JB_THEME_DIR . "/{$directory}" )
	);

	foreach ( $iterator as $file ):
		if ( $file->isDir() || $file->getExtension() != 'php' ){
			continue;
		}

		require_once( $file->getPathname() );
	endforeach;
endforeach;


//== Editor styles ( automatically generated from gulp )
add_editor_style( 'dist/styles/editor-style.css' );


//== Theme Support - Enable featured images, then remove them from pages,
// they get added to both pages and posts
add_theme_support( 'post-thumbnails' );
remove_post_type_support( 'page', 'thumbnail' );

/**
 * Gutenberg Settings
 *
 * Disable GB on the posts we don't want to use it on.
 */
add_filter( 'jb_gutenberg_disabled_posts', function( $disabled_posts ) {
	$disabled_posts[] = JB_NEWS_PAGE_ID;

	return $disabled_posts;
});


//== Nav Menus
function jb_register_nav_menus() {

	// Global menus across the site
	$menus = [];

  register_nav_menus( $menus );

}
add_action( 'after_setup_theme', 'jb_register_nav_menus', 0 );


/**
 * Content Width
 *
 * This determines the behavior of some built-in functionality such as
 * the width of oEmbed elements. It should be the inner width of the
 * content-editable region in desktop width.
 */
if ( !isset( $content_width ) ) {
	$content_width = 620;
}


/*
 * Remove end flush on the wp shutdown
 *
 * On the development server at least errors about
 * `Notice: ob_end_flush(): failed to send buffer of zlib output compression (0)`
 * were being thrown. https://core.trac.wordpress.org/ticket/22430#comment:4
 * suggested just removing the shutdown call since it seems it was only
 * required for PHP 5.2 anyway.
 */
remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );

/*
 * Contextual body classes.
 * - This will often be customized on a per-site basis, which is why
 * - it's included here rather than in core/core-functions.php.
 */
function jb_body_class( $echo = true ) {
	global $post;
	$classes = array();

	if ( is_front_page() ) {
		$classes[] = 'home';
	}

	if ( is_page() ) {
		$classes[] = 'page';
	}

	if (
		is_home() ||
		is_post_type_archive( CPT_NEWS ) ||
		is_singular( CPT_NEWS ) ||
		is_post_type_archive( CPT_NEWS ) ) {
		$classes[] = 'news';
	}

	if ( is_404() ) {
		$classes[] = 'fourohfour';
	}

	if ( is_user_logged_in() ) {
		$classes[] = 'logged-in';
	}

	// Search page
	if ( jb_is_template( 'search' ) ) {
		$classes[] = 'search';
	}

	// Apply any filters to the body classes
	$classes = apply_filters( 'jb_body_class', $classes );

	return implode( ' ', $classes );

}

//== Content Formats for the Editor
function juicebox_content_formats( $init_array ) {
	$style_formats = array(
		array(
			'title'    => 'Smaller text',
			'selector' => 'p, ul, ol, td, th',
			'classes'  => 'small-text'
		),

		array(
			'title'    => 'Alternate Link',
			'selector' => 'a',
			'classes'  => 'link--alternate'
		),

		array(
			'title'    => 'List Indent',
			'selector' => 'ol, ul, li, span, p',
			'classes'  => 'list-indent'
		),

		array(
			'title'    => 'Intro Text',
			'selector' => 'p',
			'classes'  => 'intro'
		),

		array(
			'title'    => 'Callout Text',
			'selector' => 'p',
			'classes'  => 'callout'
		),

		array(
			'title'    => 'Callout Link',
			'selector' => 'a',
			'classes'  => 'link-carot-pre--normal'
		),

		array(
			'title'    => 'Blockquote Link',
			'selector' => 'a',
			'classes'  => 'link-carot-pre'
		),

		array(
			'title' => 'Buttons',
			'items' => array(
				array(
					'title'    => 'Button Style 1',
					'selector' => 'a',
					'classes'  => 'btn'
				),

				array(
					'title'    => 'Button Style 2',
					'selector' => 'a',
					'classes'  => 'btn'
				),
			), // end button items
		), // end buttons

		// Table styles
		array(
			'title' => 'Table',
			'items' => array(
				array(
					'title'    => 'Responsive',
					'selector' => 'table',
					'classes'  => 'table table-responsive'
				),

				array(
					'title'    => 'Condensed',
					'selector' => 'table',
					'classes'  => 'table table-condensed'
				),

				array(
					'title'    => 'Striped',
					'selector' => 'table',
					'classes'  => 'table table-striped'
				),

				array(
					'title'    => 'Bordered',
					'selector' => 'table',
					'classes'  => 'table table-bordered'
				),

				array(
					'title'    => 'Smaller Text',
					'selector' => 'table',
					'classes'  => 'table table-smaller'
				),

				array(
					'title'    => 'Row Hover',
					'selector' => 'table',
					'classes'  => 'table table-hover'
				),
			) // end items
		), // end table

		// List styles
		array(
			'title'    => 'Bulleted List - Link List',
			'selector' => 'ul',
			'classes'  => 'bulleted-links'
		)
	);

	$init_array['style_formats'] = json_encode( $style_formats );

	return $init_array;
}
add_filter( 'tiny_mce_before_init', 'juicebox_content_formats' );