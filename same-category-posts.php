<?php
/*
Plugin Name: Same Category Posts
Plugin URI: https://wordpress.org/plugins/same-category-posts/
Description: Adds a widget that shows the most recent posts from a single category.
Author: DFlöter
Version: 1.0.2
Author URI: https://profiles.wordpress.org/kometschuh/
*/

// Don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register our styles
 *
 * @return void
 */
add_action( 'wp_enqueue_scripts', 'same_category_posts_styles' );

function same_category_posts_styles() {
	wp_register_style( 'same-category-posts', plugins_url( 'same-category-posts/same-category-posts.css' ) );
	wp_enqueue_style( 'same-category-posts' );
}

/**
 * Register thumbnail sizes.
 *
 * @return void
 */
if ( function_exists('add_image_size') )
{
	$sizes = get_option('kts_same_category_post_thumb_sizes');
	if ( $sizes )
	{
		foreach ( $sizes as $id=>$size )
			add_image_size( 'related_post_thumb_size' . $id, $size[0], $size[1], true );
	}
}

/**
 * Related Posts Widget Class
 *
 * Shows posts from same category with some configurable options
 */
class SameCategoryPosts extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'same-category-posts', 'description' => __('List posts from same category in sidebar based on shown post\'s category'));
		parent::__construct('same-category-posts', __('Same Category Posts'), $widget_ops);
	}

	// Displays a list of posts from same category on single post pages.
	function widget($args, $instance) {
		// Only show widget if on a post page.
		if ( !is_single() ) return;

		global $post;
		$post_old = $post; // Save the post object.
		
		extract( $args );
		
		$categories = get_the_category();
		$category = $categories[0]->cat_ID;
		
		if( !$instance["title"] ) {
			$category_info = get_category( $category );
			$instance["title"] = $category_info->name;
		}
		
		// Excerpt length filter
		$new_excerpt_length = create_function('$length', "return " . $instance["excerpt_length"] . ";");
		if ( $instance["excerpt_length"] > 0 )
			add_filter('excerpt_length', $new_excerpt_length);
		
		$valid_sort_orders = array('date', 'title', 'comment_count', 'rand');
		if ( in_array($instance['sort_by'], $valid_sort_orders) ) {
			$sort_by = $instance['sort_by'];
			$sort_order = (bool) isset( $instance['asc_sort_order'] ) ? 'ASC' : 'DESC';
		} else {
			// by default, display latest first
			$sort_by = 'date';
			$sort_order = 'DESC';
		}		
		
		$args = array(
			'cat' => $category . ',' . -$instance['exclude_category'],
			'showposts' => $instance['num'], // Number of related posts that will be shown.
			'ignore_sticky_posts' => 1,
			'orderby' => $sort_by,
			'order' => $sort_order
			);
		$my_query = new WP_Query($args);
		
		if( $my_query->have_posts() )
		{
			echo $before_widget;

			// Widget title
			if( !isset ( $instance["hide_title"] ) ) {
				echo $before_title;
				if( isset ( $instance["title_link"] ) ) {
					echo '<a href="' . get_category_link( $category ) . '">' . $instance["title"] . '</a>';
				} else {
					echo $instance["title"];
				}
				echo $after_title;
			}
			
			// Post list
			echo "<ul>\n";
			while ($my_query->have_posts())
			{
				$my_query->the_post();
				?>
				<li class="same-category-post-item">
				
					<?php 
						if( isset( $instance["thumbTop"] ) ) : 
							if (
								function_exists('the_post_thumbnail') && 
								current_theme_supports("post-thumbnails") &&
								isset ( $instance["thumb"] ) &&
								has_post_thumbnail() ) : ?>
							<a class="same-category-post-thumbnail" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
							<?php the_post_thumbnail( 'related_post_thumb_size'.$this->id ); ?>
							</a>
						<?php endif; 
						endif; 
					?>				
				
					<a class="post-title" href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a>
					
					<?php if ( isset( $instance['date'] ) ) : ?>
					<p class="post-date"><?php the_time("j M Y"); ?></p>
					<?php endif; ?>					
					
					<?php 
						if( !isset( $instance["thumbTop"] ) ) : 
							if (
								function_exists('the_post_thumbnail') && 
								current_theme_supports("post-thumbnails") &&
								isset ( $instance["thumb"] ) &&
								has_post_thumbnail() ) : ?>
							<a class="same-category-post-thumbnail" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
							<?php the_post_thumbnail( 'related_post_thumb_size'.$this->id ); ?>
							</a>
						<?php endif; 
						endif; 
					?>
					
					<?php if ( isset ( $instance['excerpt'] ) ) : ?>
					<?php the_excerpt(); ?> 
					<?php endif; ?>
					
					<?php if ( isset ( $instance['comment_num'] ) ) : ?>
					<p class="same-category-post-comment-num">(<?php comments_number(); ?>)</p>
					<?php endif; ?>
				</li>
				<?php
			}
			echo "</ul>\n";
			
			echo $after_widget;
		}

		remove_filter('excerpt_length', $new_excerpt_length);

		$post = $post_old; // Restore the post object.
	}

	/**
	 * Update the options
	 *
	 * @param  array $new_instance
	 * @param  array $old_instance
	 * @return array
	 */
	function update($new_instance, $old_instance) {
		if ( function_exists('the_post_thumbnail') )
		{
			$sizes = get_option('kts_same_category_post_thumb_sizes');
			if ( !$sizes ) $sizes = array();
			$sizes[$this->id] = array($new_instance['thumb_w'], $new_instance['thumb_h']);
			update_option('kts_same_category_post_thumb_sizes', $sizes);
		}
		
		return $new_instance;
	}

	/**
	 * The widget configuration form back end.
	 *
	 * @param  array $instance
	 * @return void
	 */
	function form($instance) {
		$instance = wp_parse_args( ( array ) $instance, array(
			'title'            => __( '' ),
			'hide_title'       => __( '' ),
			'num'              => __( '' ),
			'sort_by'          => __( '' ),
			'asc_sort_order'   => __( '' ),
			'title_link'	   => __( '' ),
			'exclude_category' => __( '' ),			
			'excerpt'          => __( '' ),
			'excerpt_length'   => __( '' ),
			'comment_num'      => __( '' ),
			'date'             => __( '' ),
			'thumb'            => __( '' ),
			'thumbTop'         => __( '' ),
			'thumb_w'          => __( '' ),
			'thumb_h'          => __( '' )
		) );

		$title            = $instance['title'];
		$hide_title       = $instance['hide_title'];
		$num              = $instance['num'];
		$sort_by          = $instance['sort_by'];
		$asc_sort_order   = $instance['asc_sort_order'];
		$title_link       = $instance['title_link'];
		$exclude_category = $instance['exclude_category'];
		$excerpt          = $instance['excerpt'];
		$excerpt_length   = $instance['excerpt_length'];
		$comment_num      = $instance['comment_num'];
		$date             = $instance['date'];
		$thumb            = $instance['thumb'];
		$thumbTop         = $instance['thumbTop'];
		$thumb_w          = $instance['thumb_w'];
		$thumb_h          = $instance['thumb_h'];		
		
			?>
			<p>
				<label for="<?php echo $this->get_field_id("title"); ?>">
					<?php _e( 'Title' ); ?>:
					<input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
				</label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id("title_link"); ?>">
					<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("title_link"); ?>" name="<?php echo $this->get_field_name("title_link"); ?>"<?php checked( (bool) $instance["title_link"], true ); ?> />
					<?php _e( 'Make widget title link' ); ?>
				</label>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id("hide_title"); ?>">
					<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("hide_title"); ?>" name="<?php echo $this->get_field_name("hide_title"); ?>"<?php checked( (bool) $instance["hide_title"], true ); ?> />
					<?php _e( 'Hide title' ); ?>
				</label>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id("num"); ?>">
					<?php _e('Number of posts to show'); ?>:
					<input style="text-align: center;" id="<?php echo $this->get_field_id("num"); ?>" name="<?php echo $this->get_field_name("num"); ?>" type="text" value="<?php echo absint($instance["num"]); ?>" size='3' />
				</label>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id("sort_by"); ?>">
					<?php _e('Sort by'); ?>:
					<select id="<?php echo $this->get_field_id("sort_by"); ?>" name="<?php echo $this->get_field_name("sort_by"); ?>">
						<option value="date"<?php selected( $instance["sort_by"], "date" ); ?>>Date</option>
						<option value="title"<?php selected( $instance["sort_by"], "title" ); ?>>Title</option>
						<option value="comment_count"<?php selected( $instance["sort_by"], "comment_count" ); ?>>Number of comments</option>
						<option value="rand"<?php selected( $instance["sort_by"], "rand" ); ?>>Random</option>
					</select>
				</label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id("asc_sort_order"); ?>">
					<input type="checkbox" class="checkbox" 
						id="<?php echo $this->get_field_id("asc_sort_order"); ?>" 
						name="<?php echo $this->get_field_name("asc_sort_order"); ?>"
						<?php checked( (bool) $instance["asc_sort_order"], true ); ?> />
							<?php _e( 'Reverse sort order (ascending)' ); ?>
				</label>
			</p>
			
			<p>
				<label>
					<?php _e( 'Exclude category' ); ?>:
					<?php wp_dropdown_categories( array( 'show_option_none' => ' ', 'name' => $this->get_field_name("exclude_category"), 'selected' => $instance["exclude_category"] ) ); ?>
				</label>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id("excerpt"); ?>">
					<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("excerpt"); ?>" name="<?php echo $this->get_field_name("excerpt"); ?>"<?php checked( (bool) $instance["excerpt"], true ); ?> />
					<?php _e( 'Show post excerpt' ); ?>
				</label>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id("excerpt_length"); ?>">
					<?php _e( 'Excerpt length (in words):' ); ?>
				</label>
				<input style="text-align: center;" type="text" id="<?php echo $this->get_field_id("excerpt_length"); ?>" name="<?php echo $this->get_field_name("excerpt_length"); ?>" value="<?php echo $instance["excerpt_length"]; ?>" size="3" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id("comment_num"); ?>">
					<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("comment_num"); ?>" name="<?php echo $this->get_field_name("comment_num"); ?>"<?php checked( (bool) $instance["comment_num"], true ); ?> />
					<?php _e( 'Show number of comments' ); ?>
				</label>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id("date"); ?>">
					<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("date"); ?>" name="<?php echo $this->get_field_name("date"); ?>"<?php checked( (bool) $instance["date"], true ); ?> />
					<?php _e( 'Show post date' ); ?>
				</label>
			</p>
			
			<?php 
				if ( function_exists('the_post_thumbnail') && current_theme_supports("post-thumbnails") ) : 
			?>
				<p>
					<label for="<?php echo $this->get_field_id("thumbTop"); ?>">
						<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("thumbTop"); ?>" name="<?php echo $this->get_field_name("thumbTop"); ?>"<?php checked( (bool) $instance["thumbTop"], true ); ?> />
						<?php _e( 'Thumbnail to top' ); ?>
					</label>
				</p>				
				<p>
					<label for="<?php echo $this->get_field_id("thumb"); ?>">
						<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("thumb"); ?>" name="<?php echo $this->get_field_name("thumb"); ?>"<?php checked( (bool) $instance["thumb"], true ); ?> />
						<?php _e( 'Show post thumbnail' ); ?>
					</label>
				</p>			
				<p>
					<label>
						<?php _e('Thumbnail dimensions (in pixels)'); ?>:<br />
						<label for="<?php echo $this->get_field_id("thumb_w"); ?>">
							Width: <input class="widefat" style="width:30%;" type="text" id="<?php echo $this->get_field_id("thumb_w"); ?>" name="<?php echo $this->get_field_name("thumb_w"); ?>" value="<?php echo $instance["thumb_w"]; ?>" />
						</label>
						
						<label for="<?php echo $this->get_field_id("thumb_h"); ?>">
							Height: <input class="widefat" style="width:30%;" type="text" id="<?php echo $this->get_field_id("thumb_h"); ?>" name="<?php echo $this->get_field_name("thumb_h"); ?>" value="<?php echo $instance["thumb_h"]; ?>" />
						</label>
					</label>
				</p>			
			<?php 
				endif; 
			?>

			<?php

	}

}

add_action( 'widgets_init', create_function('', 'return register_widget("SameCategoryPosts");') );
