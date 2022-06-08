<?php
/*
* Timber Setup
*/

$timber = new Timber\Timber();

/*
* Sets the directories (inside your theme) to find .twig files
*/
Timber::$dirname = array( 'app/views' );


/**
 * JuiceboxSite
 *
 * Extends Timber\Site to add in our own customizations for Timber
 */
class JuiceboxSite extends Timber\Site {

	public function __construct() {
		add_filter( 'timber_context', array( $this, 'add_to_context' ) );
		add_filter( 'timber/twig', array( $this, 'add_to_twig' ) );
		parent::__construct();
	}

	/*
		* Adding context to be available to all templates
		* @param string $context context['this'] Being the Twig's {{ this }}.
		*/
	public function add_to_context( $context ) {

		$context['site']             = $this;

		/**
		 * Theme Settings
		 */
		$context['WP_ENV']          = WP_ENV;
		$context['theme']->favicon	= JB_IMG_URL . '/favicon';
		$context['theme']->fonts		= JB_DIST_URL . '/fonts';
		$context['theme']->img			= JB_IMG_URL;


		/**
		 * Page Title - overrides Timber defaults
		 */
		$context['wp_title'] = wp_title( '-', false, 'right' ) . JB_SITE_NAME;


		// Nav Menus
		$nav_menu_args = [
			'depth' => 1,
		];

		$context['menus'] = [
			// 'quick_links'  	=> new \Timber\Menu( 'quick-links', $nav_menu_args ),
			// 'footer_nav'    => new \Timber\Menu( 'footer-nav', $nav_menu_args ),
			// 'social_media'	=> new \Timber\Menu( 'social-media', $nav_menu_args ),
		];

		// Main Navigation Menu
		$main_nav_items = jb_get_nav_menu_array( 'main-navigation', [
			'include_children' => false
		]);

		// Loop through the main nav items without children b/c in the context
		// of nav menus they mean the children of the nav menu not page's children.
		if ( !empty( $main_nav_items ) ):
			foreach( $main_nav_items as $item ):

				$current = ( url_to_postid( $item['url'] ) === get_queried_object_id()) ? 'current' : '';

				$context['menus']['main_navigation'][$item['ID']] = [
					'ID'          => $item['ID'],
					'cameled'     => jb_camel_case( $item['title'] ),
					'title'       => $item['title'],
					'url'         => $item['url'],
					'current'     => $current,
					'children'    => get_pages([
						'post_type'    => 'page',
						'parent'       => $item['object_id'],
						'hierarchical' => false,
						'sort_column'  => 'menu_order',
						'sort_order'   => 'asc',
					]),
				];

			endforeach;
		endif;

		return $context;
	} // end function add_to_context


	// Create functions used in twig templates
	// h/t: https://github.com/timber/timber/issues/750
	public function add_to_twig( $twig ) {

		// Script Enqueue
		$enqueue_script_function = new Twig_SimpleFunction( 'enqueue_script',
			function ( $handle, $dependency = 'global.js' ) {

			// `$handle` is passed in as a path, sanitize the title replacing the
			// slashes with dashes
			$sanitized_handle = sanitize_title( $handle );

			wp_enqueue_script(
				$sanitized_handle,
				JB_SCRIPTS_URL . "/{$handle}",
				$dependency,
				filemtime( JB_SCRIPTS_DIR . "/{$handle}" )
			);
		});

		// Style Enqueue
		$enqueue_style_function = new Twig_SimpleFunction( 'enqueue_style',
			function ( $handle, $dependency = 'main', $media = 'all' ) {

			// `$handle` is passed in as a path, sanitize the title replacing the
			// slashes with dashes
			$sanitized_handle = sanitize_title( $handle );

			wp_enqueue_style(
				$sanitized_handle,
				JB_STYLES_URL . "/{$handle}",
				$dependency,
				filemtime( JB_STYLES_DIR . "/{$handle}" ),
				$media
			);

		});

		// Register the functions
		$twig->addFunction( $enqueue_script_function );
		$twig->addFunction( $enqueue_style_function );

		return $twig;

	} // end function add_to_twig

} // end class JuiceboxSite


/**
 * JuiceboxPage
 * - Extends the default Timber\Post class with functions to
 * - get things like sidebar titles and likes with timber functions
 * `{post.sidebar_title}` and `{post.sidebar_links}`
 */
class JuiceboxPage extends Timber\Post {

	var $_ancestor;
	var $_breadcrumbs;
	var $_sidebar_title;
	var $_sidebar_links;


	public function post_ancestor( $post_id = 0, $cache = true ) {

		if ( empty( $this->_ancestor ) || $cache == false ):
			$root = false;

			// If a `post_id` has been passed in use the id of that post
			// otherwise use the current global post.
			if ( !empty( $post_id ) ) {
				$post = get_post( $post_id );
			} else {
				global $post;
			}

			// If the post has a parent find it's root ancestor, otherwise if
			// it doesn't have a parent it is it's own root.
			if ( !empty( $post->post_parent ) )	{
				$ancestors       = get_post_ancestors( $post->ID );
				$root            = count( $ancestors ) - 1;
				$this->_ancestor = $ancestors[$root];
			} elseif ( !empty( $post ) ) {
				$this->_ancestor = $post->ID;
			}

		endif;

		return $this->_ancestor;

	} // end public function post_ancestor


	public function sidebar_title( $post_id = 0 ) {

		if ( empty( $this->_sidebar_title ) ) {
			$this->_sidebar_title = get_the_title( $this->post_ancestor() );
		}

		return $this->_sidebar_title;

	} // end function sidebar_title

	/**
	 * Sidebar Links
	 *
	 * Generate the sidebar links for the given page or
	 * specified page id using `wp_list_pages`.
	 */
	public function sidebar_links( $args = [] ) {

		if ( empty( $this->_sidebar_links ) ):

			// Set some reasonable defaults
			$defaults = [
				'title_li'           => false,
				'echo'               => false,
				'post_id'            => $this->post_ancestor(),
			];

			// Parse the args with the defaults
			$args = wp_parse_args( $args, $defaults );

			/**
			 * Set the `child_of` to get all of the links for the menu we want to
			 * display, skip the ancestor cache since we want to get fresh links.
			 */
			if ( isset( $args['post_id'] ) && !empty( $args['post_id'] ) ) {
				$args['child_of'] = $this->post_ancestor( $args['post_id'], false );
			}

			$this->_sidebar_links = wp_list_pages( $args );
		endif; // end if $this->_sidebar_links

		return $this->_sidebar_links;

	} // end function sidebar_title


	/**
	 * Breadcrumbs
	 *
	 * Returns an array of breadcrumbs
	 */
	public function breadcrumbs( $args = [] ) {

		if ( empty( $this->_breadcrumbs ) ):

			/**
			 * Initalize breadcrumbs
			 */
			$breadcrumbs = [];

			// Set some reasonable defaults
			$defaults = [
				'post_id'         => $this->id,
				'post_ancestor'   => $this->post_ancestor(),
				'include_home'    => true,
				'include_current' => true,
				'home_title'      => 'Home',
				'404_title'       => 'Page not found',
				'search_title'    => 'Search results for <span class="keyword">%s</span>',
				'category_title'  => 'Articles in <span class="keyword">%s</span>',
				'date_title'      => 'Articles from <span class="keyword">%s</span>',
			];

			// Parse the args with the defaults
			$args = wp_parse_args( $args, $defaults );

			/**
			 * Set the home page link if requested
			 */
			if ( $args['include_home'] == true ):

				$breadcrumbs[] = [
					'title' => 'Home',
					'link'  => JB_SITE_URL,
				];

			endif;


			/**
			 * Four-oh-Four
			 */
			if ( is_404() ):

				$breadcrumbs[] = [
					'title' => $args['404_title'],
				];

			/**
			 * Search
			 */
			elseif ( is_search() ):

				$breadcrumbs[] = [
					'title' => sprintf( $args['search_title'], get_search_query() ),
				];


			/**
			 * Pages
			 */
			elseif ( $this->post_type == 'page' ):

				$post_ancestors = get_post_ancestors( $this->id );

				if ( !empty( $post_ancestors ) ):
					foreach ( $post_ancestors as $ancestor_id ):

						$breadcrumbs[] = [
							'title' => get_the_title( $ancestor_id ),
							'link'  => get_permalink( $ancestor_id ),
						];

					endforeach;
				endif;

			/**
			 * Post Category
			 *
			 * Adds the news page and then the current category
			 */
			elseif ( is_category() ):

				$breadcrumbs[] = [
					'title' => get_the_title( JB_NEWS_PAGE_ID ),
					'link'  => get_permalink( JB_NEWS_PAGE_ID ),
				];

				$breadcrumbs[] = [
					'title' => single_cat_title( '', false ),
				];

			/**
			 * Posts
			 */
			elseif ( $this->post_type == CPT_NEWS ):

				$breadcrumbs[] = [
					'title' => get_the_title( JB_NEWS_PAGE_ID ),
					'link'  => get_permalink( JB_NEWS_PAGE_ID ),
				];

				/**
				 * Date
				 */
				if ( is_date() ):

					$date_year   = get_query_var( 'year' );
					$date_month  = get_query_var( 'monthnum' );
					$date_format = date( 'F', mktime( 0, 0, 0, $date_month, 10 ) ) . ' ' . $date_year;

					$breadcrumbs[] = [
						'title' => sprintf(
							$args['category_title'],
							$date_format
						),
					];

				endif; // end if is_category

			endif; // end if is_404

			/**
			 * Add the current page as the last breadcrumb item
			 */
			if ( $args['include_current'] == true ):

				$breadcrumbs[] = [
					'title' => $this->title,
					'link'  => $this->link,
				];

			endif;

			/**
			 * Set the breadcrumbs to the cache variable
			 */
			$this->_breadcrumbs = $breadcrumbs;

		endif; // end if !empty $this->_breadcrumbs

		return $this->_breadcrumbs;

	} // end public function breadcrumbs

} // end class JuiceboxPage


// Go Go JuiceboxSite
new JuiceboxSite();
