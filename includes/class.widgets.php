<?php
/**
 * Static class for registing and displaying the widget.
 *
 * @package     Connections Languages
 * @subpackage  Widget
 * @copyright   Copyright (c) 2014, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class CN_Languages_Widget extends WP_Widget {

	public function __construct() {

		$options = array(
			'description' => __( 'Languages', 'connections_languages' )
		);

		parent::__construct(
			'cnlng_languages',
			'Connections : ' . __( 'Languages', 'connections_languages' ),
			$options
		);
	}

	/**
	 * Registers the widget with the WordPress Widget API.
	 *
	 * @access public
	 * @since  1.1
	 *
	 * @return void
	 */
	public static function register() {

		register_widget( __CLASS__ );
	}

	/**
	 * Logic for handling updates from the widget form.
	 *
	 * @access  private
	 * @since  1.0
	 * @param array $new
	 * @param array $old
	 *
	 * @return array
	 */
	public function update( $new, $old ) {

		$new['title'] = strip_tags( $new['title'] );

		return $new;
	}

	/**
	 * Function for handling the widget control in admin panel.
	 *
	 * @access  private
	 * @since  1.0
	 * @param array $instance
	 *
	 * @return void
	 */
	public function form( $instance ) {

		// Setup the default widget options.
		$title = isset( $instance['title'] ) && strlen( $instance['title'] ) > 0 ? esc_attr( $instance['title'] ) : __( 'Languages', 'connections_languages' );

		cnHTML::text(
			array(
				'prefix' => '',
				'class'  => 'widefat',
				'id'     => $this->get_field_id('title'),
				'name'   => $this->get_field_name('title'),
				'label'  => __('Title:', 'connections_languages'),
				'before' => '<p>',
				'after'  => '</p>',
				),
			$title
		);

	}

	/**
	 * Function for displaying the widget on the page.
	 *
	 * @access  private
	 * @since  1.0
	 * @param  array $args
	 * @param  array $instance
	 *
	 * @return string
	 */
	public function widget( $args, $option ) {

		// Only process and display the widget if displaying a single entry.
		if ( get_query_var( 'cn-entry-slug' ) ) {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();

			// Query the entry.
			$result = $instance->retrieve->entries( array( 'slug' => urldecode( get_query_var( 'cn-entry-slug' ) ) ) );

			// Setup the entry object
			$entry = new cnEntry( $result[0] );

			// Query the entry meta.
			$metadata = $entry->getMeta( array( 'key' => 'languages', 'single' => TRUE ) );

			// If there is no meta; bail.
			if ( empty( $metadata ) ) return;

			// Extract $before_widget, $after_widget, $before_title and $after_title.
			extract( $args );

			// Setup the default widget options if they were not set when they were added to the sidebar;
			// ie. the user did not click the "Save" button on the widget.
			$title  = strlen( $option['title'] ) > 0 ? $option['title'] : __( 'Languages', 'connections_languages' );

			// Setup the atts to be passed to the method that displays the data.
			$atts = array();

			echo $before_widget;

			echo $before_title . $title . $after_title;

			// Display the income level.
			Connections_Languages::block( 'languages', $metadata, NULL, $atts );

			echo $after_widget;

		}

	}

}
