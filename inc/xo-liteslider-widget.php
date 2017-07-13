<?php

class XO_Widget_Liteslider extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'liteslider',
			apply_filters( 'xo_liteslider_widget_name', __( 'Slider (XO Liteslider)', 'xo-liteslider' ) ),
			array( 'classname' => 'widget_liteslider', 'description' => __( 'Display Slider', 'xo-liteslider' ) )
		);
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		if ( isset( $instance['slider_id'] ) ) {
			$slider_id = $instance['slider_id'];
			echo do_shortcode( "[xo_liteslider id={$slider_id}]" );	
		}
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$slider_id = isset( $instance['slider_id'] ) ? intval( $instance['slider_id'] ) : 0;

		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'title' ) . '">' . __( 'Title:', 'xo-liteslider' ) . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . esc_attr( $title ) . '" />';
		echo '</p>';

		$posts = get_posts( array(
			'post_type' => 'xo_liteslider',
			'post_status' => 'publish',
			'orderby' => 'ID',
			'order' => 'ASC',
			'posts_per_page' => -1
		) );
		foreach ( $posts as $post ) {
			$sliders[] = array( 'id' => $post->ID, 'title' => $post->post_title, 'active' => ($slider_id == $post->ID ? true : false) );
		}
		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'slider_id' ) . '">' . __( 'Slider:', 'xo-liteslider' ) . '</label>';
		echo '<select id="' . $this->get_field_id( 'slider_id' ) . '" name="' . $this->get_field_name( 'slider_id' ) . '" class="widefat">';
		foreach ( $sliders as $slider ) {
			$selected = $slider['active'] ? 'selected=selected' : '';
			echo "<option value='{$slider['id']}' {$selected}>{$slider['title']}</option>";
		}
		echo '</select>';
		echo '</p>' . "\n";
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['slider_id'] = strip_tags( $new_instance['slider_id'] );
		return $instance;
	}
}
