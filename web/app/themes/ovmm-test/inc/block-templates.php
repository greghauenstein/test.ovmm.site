<?php


    function jb_block_categories( $categories, $post ) {
        return array_merge(
            $categories,
            array(
                array(
                    'slug' => 'page-layout',
                    'title' => __( 'Page Layout', 'page-layout' ),
                ),
                array(
                    'slug' => 'home-page-blocks',
                    'title' => __( 'Home Page Blocks', 'page-layout' ),
                ),
                array(
                    'slug' => 'page-components',
                    'title' => __( 'Page Components', 'page-components' ),
                ),
                array(
                    'slug' => 'wysiwyg-blocks',
                    'title' => __( 'WYSIWYG Blocks', 'wysiwyg-blocks' ),
                ),
            )
        );
    }
    add_filter( 'block_categories', 'jb_block_categories', 10, 2);

    function jb_allowed_block_types( $allowed_blocks, $post ) {

        /*
        List of default blocks, Add to array below to add to site.
        ----------------------

        --Common--

        core/paragraph
        core/image
        core/heading
        core/subhead
        core/gallery
        core/list
        core/quote
        core/audio
        core/cover
        core/file
        core/video

        -- Formatting --

        core/table
        core/verse
        core/code
        core/freeform — Classic
        core/html — Custom HTML
        core/preformatted
        core/pullquote

        -- Layout --

        core/button
        core/text-columns — Columns
        core/media-text — Media and Text
        core/more
        core/nextpage — Page break
        core/separator
        core/spacer

        -- Widgets --

        core/shortcode
        core/archives
        core/categories
        core/latest-comments
        core/latest-posts
        core/calendar
        core/rss
        core/search
        core/tag-cloud

        -- Embeds--

        core/embed
        core-embed/twitter
        core-embed/youtube
        core-embed/facebook
        core-embed/instagram
        core-embed/wordpress
        core-embed/soundcloud
        core-embed/spotify
        core-embed/flickr
        core-embed/vimeo
        core-embed/animoto
        core-embed/cloudup
        core-embed/collegehumor
        core-embed/dailymotion
        core-embed/funnyordie
        core-embed/hulu
        core-embed/imgur
        core-embed/issuu
        core-embed/kickstarter
        core-embed/meetup-com
        core-embed/mixcloud
        core-embed/photobucket
        core-embed/polldaddy
        core-embed/reddit
        core-embed/reverbnation
        core-embed/screencast
        core-embed/scribd
        core-embed/slideshare
        core-embed/smugmug
        core-embed/speaker
        core-embed/ted
        core-embed/tumblr
        core-embed/videopress
        core-embed/wordpress-tv
        */

        /*
            Gets the ACF blocks names so that we don't have to register them
            outside of using the folder. Woo!
        */
        $jb_acf_blocks = array();

        foreach (acf_get_block_types() as $block) {
            $jb_acf_blocks[] = $block['name'];
        }

        /* Array of default WP blocks you want to enable, names above. */
        $allowed_blocks = array(
            'core/freeform',
            'core/paragraph',
            'core/image',
            'core/heading',
            'core/subhead',
            'core/gallery',
            'core/list',
            'core/quote',
            'core/audio',
            'core/cover',
            'core/file',
            'core/video',
            'core/separator',
            'core/spacer',
            'core/table',
            'core/verse',
            'core/code',
            'core/freeform',
            'core/html',
            'core/preformatted',
            'core/pullquote',
			'core/buttons',
			'gravityforms/form',
        );

        /* Add Blocks only for pages or even other post types */
        if( $post->post_type === 'page' ) {
            $allowed_blocks[] = 'core/shortcode';
        }

        /* Combine the block arrays back together */
        $allowed_blocks = array_merge($allowed_blocks, $jb_acf_blocks);

        return $allowed_blocks;
    }
    add_filter( 'allowed_block_types', 'jb_allowed_block_types', 10, 2 );

