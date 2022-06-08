<?php
/**
 * The Template for displaying the front page
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context         = Timber::get_context();
$post            = Timber::query_post();
$context['post'] = $post;


Timber::render( 'pages/front.twig', $context );
