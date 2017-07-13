<?php
/*
Plugin Name: XO Liteslider
Plugin URI: https://xakuro.com/wordpress/
Description: XO Liteslider plugin is a responsive support content slider.
Author: Xakuro System
Author URI: https://xakuro.com/
License: GPLv2
Version: 1.3.1
Text Domain: xo-liteslider
Domain Path: /languages/
*/

class XO_Liteslider {

	public function __construct() {
		load_plugin_textdomain( 'xo-liteslider', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		require_once( plugin_dir_path( __FILE__ ) . 'inc/xo-liteslider-widget.php' );

		if ( function_exists( 'register_uninstall_hook' ) ) {
			register_uninstall_hook( __FILE__, 'XO_Liteslider::uninstall' );
		}

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * テーブルおよびオプション設定を削除します。プラグインを削除したときに実行されます。
	 */
	public static function uninstall() {
		global $wpdb;
		$posts_table = $wpdb->posts;
		$query = "DELETE FROM {$posts_table} WHERE post_type = 'xo_liteslider';";
		$wpdb->query( $query );
	}

	public function plugins_loaded() {
        add_action( 'widgets_init', array( $this, 'register_widget' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ) );
		add_shortcode( 'xo_liteslider', array( $this, 'get_shortcode' ) );
	}

	public function enqueue_style() {
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'liteslider', plugins_url( 'css/liteslider.css', __FILE__ ) );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'liteslider', plugins_url( 'js/liteslider.js', __FILE__ ), array( 'jquery' ), false, false );
		wp_enqueue_script( 'xo-liteslider', plugins_url( 'js/xo-liteslider.js', __FILE__ ), array( 'liteslider' ), false, false );
	}

	private function get_script_code( $id, $parameters ) {
		$effect = $parameters['effect'];
		$navigation = !empty( $parameters['navigation'] ) ? 'true' : 'false';
		$pagination = !empty( $parameters['pagination'] ) ? 'true' : 'false';
		$slideshow = !empty( $parameters['slideshow'] ) ? 'true' : 'false';
		$slideshow_speed = $parameters['slideshow_speed'];
		$animation_speed = $parameters['animation_speed'];

$html = <<<EOM
<script type="text/javascript">
  var xo_liteslider;
  if (!(xo_liteslider != null && typeof xo_liteslider === "object" && 'pop' in xo_liteslider && 'join' in xo_liteslider)) {
    xo_liteslider = [];
  }
  xo_liteslider.push( function($) {
    jQuery("#xo-liteslider-$id").liteslider({
      effect: "$effect",
      navigation: $navigation,
      pagination: $pagination,
      slideshow: $slideshow,
      slideshowSpeed: $slideshow_speed,
      animationSpeed: $animation_speed,
      navigationPrev: "<span class=\"dashicons dashicons-arrow-left-alt2\"></span>",
      navigationNext: "<span class=\"dashicons dashicons-arrow-right-alt2\"></span>"
    });
  });
</script>
EOM;
		return $html;
	}

	function get_slider( $id = 0 ) {
		if ( $id === 0 ) {
			// ID の指定がない場合、最初のスライドを使用する。
			$slider_posts = get_posts( array( 'post_type' => 'xo_liteslider', 'orderby' => 'date', 'order' => 'ASC', 'numberposts' => 1 ) );
			if ( $slider_posts ) {
				$id = $slider_posts[0]->ID;
			}
		}

		$slider = get_post( $id );
		if ( ! $slider || $slider->post_status != 'publish' || $slider->post_type != 'xo_liteslider' ) {
			return;
		}

		$slides = get_post_meta( $id, 'slides', true );
		if ( empty( $slides ) ) {
			return;
		}

		$this->enqueue_scripts();

		$parameters = get_post_meta( $id, 'parameters', true );
		$width = ( empty( $parameters['width'] ) ) ? '' : sprintf(' width="%d"', $parameters['width'] );
		$height = ( empty( $parameters['height'] ) ) ? '' : sprintf(' height="%d"', $parameters['height'] );

		switch ( $parameters['sort'] ) {
			case 'random': shuffle( $slides ); break;
			case 'desc': $slides = array_reverse( $slides ); break;
		}

		$slides_html = '';
		$slide_counter = 0;
		foreach ( $slides as $key => $value ) {
			$img_html = '';
			if ( !empty( $value['image_id'] ) && $value['image_id'] !== 0 ) {
				$image_src = wp_get_attachment_image_src( $value['image_id'], 'full' );
				$title = empty( $value['title'] ) ? '' : ' title="' . $value['title'] . '"';
				$alt = empty( $value['alt'] ) ? '' : ' alt="' . $value['alt'] . '"';
				$img_html .= "<img class=\"slide-image\" src=\"{$image_src[0]}\"{$alt}{$title}{$width}{$height} />";
			}
			if ( $img_html != '') {
				$slides_html .= '<li>';
				$link = $image_name = $value['link'];
				if ( empty( $link ) ) {
					$slides_html .= $img_html;
				} else {
					$slides_html .= '<a href="' . $link . '">' . $img_html . '</a>';
				}
				if ( !empty( $parameters['content'] ) ) {
					$slides_html .= '<div class="slide-content">';
					$slides_html .= $value['content'];
					$slides_html .= '</div>';
				}
				$slides_html .= '</li>';
				$slide_counter++;
			}
		}

		$html = '';
		if ( $slides_html !== '' ) {
			$style = ( !empty( $parameters['width'] ) ) ? sprintf(' style="max-width: %dpx;"', $parameters['width'] ) : '';
			$class = ( !empty( $parameters['effect'] ) ) ? ' liteslider-' . $parameters['effect'] : '';

			$html .= '<div id="xo-liteslider-' . $id . '" class="liteslider' . $class . '"' . $style . '>';
			$html .= '<div class="slides-container">';
			$html .= '<ul class="slides">';
			$html .= $slides_html;
			$html .= '</ul>';
			$html .= '</div>';
			$html .= '</div>' . "\n";
			$html .= $this->get_script_code( $id, $parameters );
		}
		return $html;
	}

	public function get_shortcode( $atts ) {
		extract( shortcode_atts( array(
			'id' => 0
		), $atts, 'xo_liteslider' ) );
		return $this->get_slider( $id );
	}

	public function register_widget() {
		register_widget( 'XO_Widget_Liteslider' );
	}
}

$xo_liteslider = new XO_Liteslider();

function xo_liteslider( $id = 0 ) {
	global $xo_liteslider;
	echo $xo_liteslider->get_slider( $id );
}

if ( is_admin() ) {
	require_once( plugin_dir_path( __FILE__ ) . 'inc/admin.php' );
	new XO_Liteslider_Admin();
}
