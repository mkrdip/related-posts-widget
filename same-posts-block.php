<?php
/**
 * Gutenberg Block implementation.
 *
 * @package samePosts.
 *
 * @since 4.9
 */

namespace samePosts;

/**
 * Renders the `tiptip/same-posts-block` on server.
 *
 * @see WP_Widget_Archives
 *
 * @param array $attributes The block attributes.
 *
 * @return string Returns the post content with archives added.
 */
function render_same_posts_block( $attributes ) {
	global $attr, $before_title, $after_title;

	$attr = $attributes;

	$show_post_count = ! empty( $attributes['showPostCounts'] );

	$class = '';

	if ( ! empty( $attributes['displayAsDropdown'] ) ) {

		$class .= ' same-posts-block-dropdown';

		$dropdown_id = esc_attr( uniqid( 'same-posts-block-' ) );
		$title       = __( 'Archives' );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
		$dropdown_args = apply_filters(
			'widget_archives_dropdown_args',
			array(
				'type'            => $attributes['groupBy'],
				'format'          => 'option',
				'show_post_count' => $show_post_count,
				'order'           => $attributes['order'],
			)
		);

		$dropdown_args['echo'] = 0;

		$archives = wp_get_archives( $dropdown_args );

		switch ( $dropdown_args['type'] ) {
			case 'yearly':
				$label = __( 'Select Year' );
				break;
			case 'monthly':
				$label = __( 'Select Month' );
				break;
			case 'daily':
				$label = __( 'Select Day' );
				break;
			case 'weekly':
				$label = __( 'Select Week' );
				break;
			default:
				$label = __( 'Select Post' );
				break;
		}

		$label = esc_html( $label );

		$block_content = '<label class="screen-reader-text" for="' . $dropdown_id . '">' . $title . '</label>
	<select id="' . $dropdown_id . '" name="archive-dropdown" onchange="document.location.href=this.options[this.selectedIndex].value;">
	<option value="">' . $label . '</option>' . $archives . '</select>';

		return sprintf(
			'<div class="%1$s">%2$s</div>',
			esc_attr( $class ),
			$block_content
		);
	}

	$class .= ' same-posts-block-list';

	/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
	$archives_args = apply_filters(
		'widget_archives_args',
		array(
			'type'            => $attributes['groupBy'],
			'format'          => 'custom',
			'show_post_count' => $show_post_count,
			'order'           => $attributes['order'],
		)
	);

	$archives_args['echo'] = 0;
	
	$archives = wp_get_archives( $archives_args );

	$classnames = esc_attr( $class );

	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $classnames ) );

	if ( empty( $archives ) ) {
		return sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attributes,
			__( 'No archives to show.' )
		);
	}

	// Get HTML from old code
	$widget = new Widget();
	$instance = array();

	$instance['asc_sort_order'] = $attributes['order'] === 'desc' ? false : true;
	$instance['title']          = $attributes['title'];

	//$instance = upgrade_settings( $instance );
	
	$current_post_id = '';
	if ( is_singular() ) {
		$current_post_id = get_the_ID();
	}

	// $items = $widget->get_elements_HTML( $instance, $current_post_id, 0, 0 );

	// $ret = $widget->titleHTML( $before_title, $after_title, $instance );

	// $ret .= "<ul>" . implode( $items ) . "</ul>";

	// return $ret;



	$ret = $widget->itemHTML( $instance, $current_post_id );
	return $ret;
}

/**
 * Registers all block assets so that they can be enqueued through the block editor
 * in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
// function same_posts_block_init() {
// 	$dir = __DIR__;

// 	$script_asset_path = "$dir/build/index.asset.php";
// 	if ( ! file_exists( $script_asset_path ) ) {
// 		throw new Error(
// 			'You need to run `npm start` or `npm run build` for the "tiptip/same-posts-block" block first.'
// 		);
// 	}
// 	$index_js     = 'build/index.js';
// 	$script_asset = require( $script_asset_path );
// 	wp_register_script(
// 		'tiptip-same-posts-block-editor',
// 		plugins_url( $index_js, __FILE__ ),
// 		$script_asset['dependencies'],
// 		$script_asset['version']
// 	);
// 	wp_set_script_translations( 'tiptip-same-posts-block-editor', 'same-posts' );

// 	$editor_css = 'build/style-index.css';
// 	wp_register_style(
// 		'tiptip-same-posts-block-editor',
// 		plugins_url( $editor_css, __FILE__ ),
// 		array(),
// 		filemtime( "$dir/$editor_css" )
// 	);

// 	$style_css = 'build/style-index.css';
// 	wp_register_style(
// 		'tiptip-same-posts-block',
// 		plugins_url( $style_css, __FILE__ ),
// 		array(),
// 		filemtime( "$dir/$style_css" )
// 	);

// 	register_block_type(
// 		'tiptip/same-posts-block',
// 		array(
// 			'editor_script' => 'tiptip-same-posts-block-editor',
// 			'editor_style'  => 'tiptip-same-posts-block-editor',
// 			'style'         => 'tiptip-same-posts-block',
// 			'attributes'    => array(
// 				'hideTitle' => array(
// 					'type'    => 'boolean',
// 					'default' => false,
// 				),
// 				'title' => array(
// 					'type'    => 'string',
// 					'default' => __( 'Same Posts', 'same-posts' ),
// 				),
// 				'titleLink' => array(
// 					'type'    => 'boolean',
// 					'default' => false,
// 				),
// 				'disableCSS' => array(
// 					'type'           => 'boolean',
// 					'default'        => false,
// 				),
// 				'disableFontStyles' => array(
// 					'type'           => 'boolean',
// 					'default'        => false,
// 				),
// 				'disableThemeStyles' => array(
// 					'type'           => 'boolean',
// 					'default'        => false,
// 				),

// 				'showPostCounts' => array(
// 					'type'           => 'boolean',
// 					'default'        => false,
// 				),
// 				'displayAsDropdown' => array(
// 					'type'           => 'boolean',
// 					'default'        => false,
// 				),
// 				'groupBy' => array(
// 					'type'    => 'string',
// 					'default' => 'monthly',
// 				),
// 				'order' => array(
// 					'type'    => 'string',
// 					'default' => 'desc',
// 				),
// 				'orderBy' => array(
// 					'type'    => 'string',
// 					'default' => 'date',
// 				),
// 				'categorySuggestions' => array(
// 					'type'    => 'array',
// 					'default' => [],
// 				),
// 				'selectCategories' => array(
// 					'type'    => 'array',
// 					'default' => '',
// 				),
// 				'categories' => array(
// 					'type'    => 'array',
// 					'default' => [],
// 				),
// 			),
// 			'render_callback' => __NAMESPACE__ . '\render_same_posts_block',
// 		)
// 	);
// }
// add_action( 'init', __NAMESPACE__ . '\same_posts_block_init' );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/writing-your-first-block-type/
 */
function same_posts_block_init() {
	// register_block_type_from_metadata( __DIR__ );

	register_block_type( 
		__DIR__, 
		array(
			'render_callback' => __NAMESPACE__ . '\render_same_posts_block'
			) 
	);
}
add_action( 'init', __NAMESPACE__ . '\same_posts_block_init' );
