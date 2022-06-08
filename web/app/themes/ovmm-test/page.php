<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * To generate specific templates for your pages you can use:
 * app/views/pages/mypage.twig (which will still route through this PHP file)
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context         = Timber::get_context();
$post            = new JuiceboxPage();
$context['post'] = $post;


Timber::render(
  [
    'pages/' . $post->post_name . '.twig',
    'pages/interior.twig'
	],
  $context
);