<?php
/**
 * Responsive Video Embeds
 * - Default to using responsive video embeds for all videos
 * h/t: https://developer.wordpress.org/reference/hooks/embed_oembed_html/#comment-1964
 */

function jb_wrap_oembed_html( $cached_html, $url, $attr, $post_id ) {
	$html = <<<HTML
  <div class="embed-responsive embed-responsive-16by9">
    {$cached_html}
  </div>
HTML;

	return $html;
}

add_filter( 'embed_oembed_html', 'jb_wrap_oembed_html', 10, 4 );
add_filter( 'video_embed_html', 'jb_wrap_oembed_html' ); // Jetpack