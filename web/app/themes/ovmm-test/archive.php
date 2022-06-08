<?php
/**
 * The template for displaying Archive pages.
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context          = Timber::get_context();
$context['posts'] = new Timber\PostQuery();


Timber::render(
	[
		'archives/' . str_replace( '_', '-', get_post_type() ) . '.twig',
		'archives/news.twig'
	],
	$context
);