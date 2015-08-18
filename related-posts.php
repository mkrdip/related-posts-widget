<?php
/*
Plugin Name: Related Posts Widget
Plugin URI: https://wordpress.org/plugins/related-posts-widget/
Description: Adds a widget that shows a list of related posts in single post pages.
Author: Mrinal Kanti Roy	
Version: 2.0.1
Author URI: http://profiles.wordpress.org/mkrdip/
*/

// Don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register our styles
 *
 * @return void
 */
add_action( 'wp_enqueue_scripts', 'related_posts_widget_styles' );

function related_posts_widget_styles() {
	wp_register_style( 'related-posts-widget', plugins_url( 'related-posts-widget/related-posts.css' ) );
	wp_enqueue_style( 'related-posts-widget' );
}

/**
 * Register thumbnail sizes.
 *
 * @return void
 */
if ( function_exists('add_image_size') )
{
	$sizes = get_option('mkrdip_related_post_thumb_sizes');
	if ( $sizes )
	{
		foreach ( $sizes as $id=>$size )
			add_image_size( 'related_post_thumb_size' . $id, $size[0], $size[1], true );
	}
}

/**
 * Related Posts Widget Class
 *
 * Shows the related posts with some configurable options
 */
class RelatedPosts extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'rel-post-widget', 'description' => __('List related posts in sidebar based on tag'));
		parent::__construct('related-posts', __('Related Posts'), $widget_ops);
	}

	// Displays a list of related posts on single post pages.
	function widget($args, $instance) {
		// Only show widget if on a post page.
		if ( !is_single() ) return;

		global $post;
		$post_old = $post; // Save the post object.
		
		extract( $args );
		
		if( !$instance["title"] )
			$instance["title"] = "Related Posts";
		
		// Excerpt length filter
		$new_excerpt_length = create_function('$length', "return " . $instance["excerpt_length"] . ";");
		if ( $instance["excerpt_length"] > 0 )
			add_filter('excerpt_length', $new_excerpt_length);

		$tags = wp_get_post_tags($post->ID);

		if ($tags) {
			$tag_ids = array();
			foreach($tags as $individual_tag) $tag_ids[] = $individual_tag->term_id;
		
			$args=array(
				'tag__in' => $tag_ids,
				'post__not_in' => array($post->ID),
				'showposts'=> $instance['num'], // Number of related posts that will be shown.
				'ignore_sticky_posts'=>1
				);
			$my_query = new WP_Query($args);
			if( $my_query->have_posts() )
			{
				echo $before_widget;

				// Widget title
				echo $before_title . $instance["title"] . $after_title;
				
				// Post list
				echo "<ul>\n";
				while ($my_query->have_posts())
				{
					$my_query->the_post();
					?>
					<li class="related-post-item">
						<a class="post-title" href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a>
						
						<?php if ( isset( $instance['date'] ) ) : ?>
						<p class="post-date"><?php the_time("j M Y"); ?></p>
						<?php endif; ?>					
						
						<?php
							if (
								function_exists('the_post_thumbnail') && 
								current_theme_supports("post-thumbnails") &&
								isset ( $instance["thumb"] ) &&
								has_post_thumbnail()
							) :
						?>
							<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
							<?php the_post_thumbnail( 'related_post_thumb_size'.$this->id ); ?>
							</a>
						<?php endif; ?>
						
						<?php if ( isset ( $instance['excerpt'] ) ) : ?>
						<?php the_excerpt(); ?> 
						<?php endif; ?>
						
						<?php if ( isset ( $instance['comment_num'] ) ) : ?>
						<p class="comment-num">(<?php comments_number(); ?>)</p>
						<?php endif; ?>
					</li>
					<?php
				}
				echo "</ul>\n";
				
				echo $after_widget;
			}
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
			$sizes = get_option('mkrdip_related_post_thumb_sizes');
			if ( !$sizes ) $sizes = array();
			$sizes[$this->id] = array($new_instance['thumb_w'], $new_instance['thumb_h']);
			update_option('mkrdip_related_post_thumb_sizes', $sizes);
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
			'title'          => __( '' ),
			'num'            => __( '' ),
			'title_link'	 => __( '' ),
			'excerpt'        => __( '' ),
			'excerpt_length' => __( '' ),
			'comment_num'    => __( '' ),
			'date'           => __( '' ),
			'thumb'          => __( '' ),
			'thumb_w'        => __( '' ),
			'thumb_h'        => __( '' )
		) );

		$title          = $instance['title'];
		$num            = $instance['num'];
		$title_link		= $instance['title_link'];		
		$excerpt        = $instance['excerpt'];
		$excerpt_length = $instance['excerpt_length'];
		$comment_num    = $instance['comment_num'];
		$date           = $instance['date'];
		$thumb          = $instance['thumb'];
		$thumb_w        = $instance['thumb_w'];
		$thumb_h        = $instance['thumb_h'];		
		
			?>
			<p>
				<label for="<?php echo $this->get_field_id("title"); ?>">
					<?php _e( 'Title' ); ?>:
					<input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
				</label>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id("num"); ?>">
					<?php _e('Number of posts to show'); ?>:
					<input style="text-align: center;" id="<?php echo $this->get_field_id("num"); ?>" name="<?php echo $this->get_field_name("num"); ?>" type="text" value="<?php echo absint($instance["num"]); ?>" size='3' />
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
			
			<?php if ( function_exists('the_post_thumbnail') && current_theme_supports("post-thumbnails") ) : ?>
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
			<?php endif; ?>

			<?php

	}

}

add_action( 'widgets_init', create_function('', 'return register_widget("RelatedPosts");') );

