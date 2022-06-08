<?php

/* The purpose of this file is to give a place to enable and disable non-block related gutenberg options */

//== Gutenberg Initialization
add_filter( 'timber/acf-gutenburg-blocks-templates', function( $directories ) {

	return [
		get_stylesheet_directory() . '/app/views/blocks',
		get_stylesheet_directory() . '/app/views/partials'
	];

});

/**
 * Gutenberg Enabler
 *
 * By default enable all on all pages, disable specific post_ids
 * with the filter.
 */
add_filter( 'use_block_editor_for_post', function() {
	$page_id       = get_the_ID();
	$post_type     = get_post_type( $page_id );
	$use_gutenberg = false;

	/**
	 * Disable Gutenberg certain posts
	 *
	 * Array of posts to disable GB on, normally certain templates
	 * like events, directory listings where there is no user editable
	 * content anyway.
	 *
	 * Anyone using the filter should combine, not replace the array
	 * of post_ids.
	 */
	$gutenberg_disabled_posts = apply_filters( 'jb_gutenberg_disabled_posts', [] );

	if ( in_array( $page_id, $gutenberg_disabled_posts ) ) {
		$use_gutenberg = false;
	} else if ( $post_type == "page" ) {
		$use_gutenberg = true;
	}

	return $use_gutenberg;

});

add_action( 'in_admin_header', 'jb_gutenberg_wrapper' );
function jb_gutenberg_wrapper() {
	// Don't run this wrapper script if gutenberg is disabled!
	if ((method_exists(get_current_screen(), 'is_block_editor') && get_current_screen()->is_block_editor()) != true){
		return false;
	}

	global $pagenow;

	if ( $pagenow == 'post-new.php' || $pagenow == 'post.php') {
		$context = Timber::context();
		$post = get_post(get_the_id());

		echo '<div id="template-in-edit-wrapper" style="opacity:0;">';
		Timber::render(
			[ 	'templates/' . pathinfo(get_page_template_slug())['filename'] . '.twig',
				'pages/' . $post->post_name . '.twig',
				'pages/page.twig'
			],
			$context
		);
		echo '</div>';

		wp_enqueue_script(
			'admin-post-js',
			JB_SCRIPTS_URL . '/gutenberg-changes.js',
			[],
			filemtime( JB_SCRIPTS_DIR . '/gutenberg-changes.js' )
		);

		echo "<style>
				#template-in-edit-wrapper {max-width: calc(100% - 280px);opacity: 0-;transition:.5s ease all;margin-left: 0px;overflow-x: hidden;}
				.block-editor-block-types-list__list-item:before {display:none !important}
				.components-font-size-picker ~ .components-base-control.components-toggle-control {display: none !important;}

				.block-editor-editor-skeleton {position: static; }

				.block-editor-editor-skeleton__header {position: fixed;top: 32px;left: 160px;width: calc(100% - 160px);z-index: 1000;}

				.edit-post-sidebar{position: fixed;top: 89px;right: 0;border-left: 1px solid #e2e4e7;padding-bottom: 260px;}

				.block-editor-editor-skeleton__sidebar {border-left: none !important;z-index: 1000 !important;}
				#adminmenuback {z-index: 1000;}
				#adminmenuwrap {position: fixed;}
				.editor-post-title {padding-left: 0; padding-right: 0;}
				.edit-post-visual-editor{padding-top:0;}
				.editor-styles-wrapper>:first-child{margin-top:0 !important;}
				.block-editor__container { position: static; min-height: 160px !important; }
				.block-editor-editor-skeleton__body {overscroll-behavior-y:auto;}
				.block-editor-editor-skeleton__content {overflow: hidden;}
				.block-editor-editor-skeleton__footer {display: none;}
				.editor-styles-wrapper .editor-post-title {margin-top:0 !important;}
				.block-editor-writing-flow__click-redirect {min-height: 10vh !important;}
				.components-editor-notices__dismissible {top: 89px;left: 160px;right: auto;border-right: 1px solid #cbc6be;position: fixed;}
				.block-editor-block-list__layout .block-editor-block-list__block.is-selected::before { border: none !important; outline: 2px solid rgb(128 128 128 / 11%); top: 0; left: 2px; right: 2px; bottom: 0; box-shadow: none !important; }

				.block-editor-block-list__block[data-type*='acf'] {margin-top:0 !important;margin-bottom:0 !important;}

				.interface-interface-skeleton__sidebar {border-left: none !important;}
				.interface-interface-skeleton__body {overscroll-behavior-y: auto;}
				.interface-interface-skeleton__footer {display: none;}
				.interface-interface-skeleton {position: static;}
				.interface-interface-skeleton__header {position: fixed;top: 31px;left: 160px;z-index: 500;}
				.interface-interface-skeleton__content {overflow:hidden;}
				.edit-post-layout__metaboxes { position: fixed; bottom: 0; width: calc(100% - 440px); }
				.interface-interface-skeleton__left-sidebar { position: fixed !important;top: 90px;width: 340px;left: 160px;height: calc(100% - 90px);box-shadow: 0px 0px 10px rgb(0 0 0 / 0.25);}
				</style>";

		if ($post->post_name == 'home') {
			echo "<style>
				.editor-post-title {display:none;}
				</style>";
		}
	}
}

// Add the ability to have our block styles copied over.
add_theme_support('editor-styles');


//== Editor styles ( automatically generated from gulp ).
// This file is intended for the text editor styles only.
add_editor_style( 'dist/styles/editor-style.css' );

if (is_admin()) {
	function jb_disable_editor_fullscreen_by_default() {
	    $script = "jQuery( window ).load(function() { isFullscreenMode = wp.data.select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' ); if ( isFullscreenMode ) { wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' ); } });";
	    wp_add_inline_script( 'wp-blocks', $script );
	}
	add_action( 'enqueue_block_editor_assets', 'jb_disable_editor_fullscreen_by_default' );
}

function jb_default_block_settings_changes() {
	add_theme_support( 'disable-custom-colors' );

	// Add in custom block padding. Yay!
	add_theme_support('experimental-custom-spacing');

	// Fixing up the paragraph block.
	// -- Remove font sizes.
	add_theme_support( 'disable-custom-font-sizes' );

	// -- Remove colors
	add_theme_support( 'editor-color-palette' );

	// Font sizes in the editor.
	add_theme_support( 'editor-font-sizes', array(
		array(
			'name'      => __( 'small', 'jb-fonts' ),
			'shortName' => __( 'S', 'jb-fonts' ),
			'size'      => 12,
			'slug'      => 'small'
		),
		array(
			'name'      => __( 'intro', 'jb-fonts' ),
			'shortName' => __( 'Intro', 'jb-fonts' ),
			'size'      => 22,
			'slug'      => 'intro'
		)
	) );

	// -- Disable Gradients
	add_theme_support( 'disable-custom-gradients' );
	remove_theme_support( 'core-block-patterns' );
	add_theme_support( 'editor-gradient-presets', array() );
}
add_action( 'after_setup_theme', 'jb_default_block_settings_changes' );


add_action('init', function(){
	// Button Styles
	register_block_style(
		'core/button',
		array(
			'name'         => 'orange',
			'label'        => 'Orange Button'
		)
	);
});