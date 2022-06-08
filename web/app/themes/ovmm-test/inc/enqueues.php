<?php
/*
 * CSS and JS Enqueues
 */

function jb_base_enqueues() {
	// Remove core gutenberg styles
	// wp_dequeue_style( 'wp-block-library' ); // Wordpress core
    // wp_dequeue_style( 'wp-block-library-theme' ); // Wordpress core

	// Main CSS
	wp_enqueue_style(
		'main-styles',
		JB_STYLES_URL . '/main.css',
		[],
		filemtime( JB_STYLES_DIR . '/main.css' ),
		'all'
	);

	// Global js
	wp_enqueue_script(
		'global-js',
		JB_SCRIPTS_URL . '/global.js',
		[],
		filemtime( JB_SCRIPTS_DIR . '/global.js' )
	);
}
add_action( 'wp_enqueue_scripts', 'jb_base_enqueues' );
