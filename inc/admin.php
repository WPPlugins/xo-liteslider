<?php

class XO_Liteslider_Admin {
	
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	public function plugins_loaded() {
		add_action( 'init', array( $this, 'register_xo_liteslider_type' ) );
		add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );
		add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_updated_messages' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'remove_row_actions' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );
	}

	public function admin_enqueue_scripts() {
		global $post;
		if ( isset ( $post ) ) {
			wp_enqueue_style( 'xo-liteslider-admin', plugins_url( '../css/admin.css', __FILE__ ) );
			// メディアアップローダー用のスクリプトをロードする。
			wp_enqueue_media( array( 'post' => $post->ID ) );
			wp_enqueue_script( 'xo-liteslider-admin', plugins_url( '../js/admin.js', __FILE__ ), array( 'jquery' ), null, true );
			wp_localize_script( 'xo-liteslider-admin', 'messages', array(
				'title' => __( 'Select Image', 'xo-liteslider' )
			) );
		}
	}

	public function register_xo_liteslider_type() {
		register_post_type( 'xo_liteslider', array(
			'labels' => array(
				'name' => __( 'Sliders', 'xo-liteslider' ),
				'singular_name' => __( 'Slider', 'xo-liteslider' ),
				'menu_name' => apply_filters( 'xo_liteslider_menu_name', __( 'Sliders', 'xo-liteslider' ) ),
				'name_admin_bar' => apply_filters( 'xo_liteslider_name_admin_bar', __( 'Sliders', 'xo-liteslider' ) ),
				'all_items' => __( 'All Sliders', 'xo-liteslider' ),
				'add_new' => __( 'Add New', 'xo-liteslider' ),
				'add_new_item' => __( 'Add New', 'xo-liteslider' ),
				'edit_item' => __( 'Edit Slider', 'xo-liteslider' ),
				'new_item' => __( 'New Slider', 'xo-liteslider' ),
				'view_item' => __( 'View Slider', 'xo-liteslider' ),
				'search_items' => __( 'Search Slider', 'xo-liteslider' ),
				'not_found' => __( 'No sliders found', 'xo-liteslider' ),
				'not_found_in_trash' => __( 'No sliders found in Trash', 'xo-liteslider' ),
			),
			'public' => false,
			'exclude_from_search' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => false,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => apply_filters( 'xo_liteslider_menu_position', 25 ),
			'menu_icon' => apply_filters( 'xo_liteslider_menu_icon', 'dashicons-slides' ),
			'supports' => array( 'title', 'author' )
		) );
	}

	public function updated_messages( $messages ) {
		global $post;
		$messages['xo_liteslider'] = array(
			0 => '',
			1 => __( 'Slider updated.', 'xo-liteslider' ),
			2 => __( 'Custom field updated.', 'xo-liteslider' ),
			3 => __( 'Custom field deleted.', 'xo-liteslider' ),
			4 => __( 'Slider updated.', 'xo-liteslider' ),
			5 => isset( $_GET['revision'] ) ? sprintf( __( 'Slider restored to revision from %s.', 'xo-liteslider' ), wp_post_revision_title( (int)$_GET['revision'], false ) ) : false,
			6 => __( 'Slider published.', 'xo-liteslider' ),
			7 => __( 'Slider saved.', 'xo-liteslider' ),
			8 => __( 'Slider submitted.', 'xo-liteslider' ),
			9 => sprintf( __( 'Slider scheduled for: <strong>%1$s</strong>.', 'xo-liteslider' ), date_i18n( __( 'M j, Y @ G:i', 'xo-liteslider' ), strtotime( $post->post_date ) ) ),
			10 => __( 'Slider draft updated.', 'xo-liteslider' ),
		);
		return $messages;
	}

	public function bulk_updated_messages( $bulk_messages, $bulk_counts ) {
		$bulk_messages['xo_liteslider'] = array(
			'updated' => _n( '%s slider updated.', '%s sliders updated.', $bulk_counts['updated'], 'xo-liteslider' ),
			'locked' => _n( '%s slidert not updated, somebody is editing it.', '%s slider not updated, somebody is editing them.', $bulk_counts['locked'], 'xo-liteslider' ),
			'deleted' => _n( '%s slidert permanently deleted.', '%s sliders permanently deleted.', $bulk_counts['deleted'], 'xo-liteslider' ),
			'trashed' => _n( '%s slider moved to the Trash.', '%s sliders moved to the Trash.', $bulk_counts['trashed'], 'xo-liteslider' ),
			'untrashed' => _n( '%s slider restored from the Trash.', '%s sliders restored from the Trash.', $bulk_counts['untrashed'], 'xo-liteslider' ),
		);
		return $bulk_messages;
	}

	public function remove_row_actions( $actions, $post ) {
		if ( $post->post_type != 'xo_liteslider' )
			return $actions;
		unset( $actions['inline hide-if-no-js'] );
		return $actions;
	}

	public function save_post( $post_id ) {
		if ( !isset( $_POST['xo_liteslider_nonce'] ) || !check_admin_referer( 'xo_liteslider_key', 'xo_liteslider_nonce' ) || !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
		if ( isset( $_POST['xo_liteslider_slides'] ) ) {
			$slides = $_POST['xo_liteslider_slides'];
			update_post_meta( $post_id, 'slides', $slides );
		}
		if ( isset( $_POST['xo_liteslider_parameters'] ) ) {
			$parameters = $_POST['xo_liteslider_parameters'];
			update_post_meta( $post_id, 'parameters', $parameters );
		}
	}

	public function admin_init() {
		remove_meta_box( 'submitdiv', 'xo_liteslider', 'core' );
		add_meta_box( 'xo-liteslider-meta-slide', __( 'Slides', 'xo-liteslider'), array( $this, 'display_meta' ), 'xo_liteslider', 'normal', 'high' );
		add_meta_box( 'xo-liteslider-meta-parameter', __( 'Parameter', 'xo-liteslider' ), array( $this, 'display_meta_parameter' ), 'xo_liteslider', 'side', 'low' );
		add_meta_box( 'xo-liteslider-meta-usage', __( 'Usage', 'xo-liteslider' ), array( $this, 'display_meta_usage' ), 'xo_liteslider', 'side', 'low' );
		add_meta_box( 'submitdiv', __( 'Save', 'xo-liteslider' ), array( $this, 'submit_meta_box' ), 'xo_liteslider', 'side', 'high', null );
	}

	public function submit_meta_box( $post, $args = array() ) {
		echo '<div class="submitbox" id="submitpost">';
		echo '<div id="major-publishing-actions" style="border-top: 0;">';

		echo '<div style="display:none;">' . get_submit_button( __( 'Save' ), 'button', 'save' ) . '</div>';
		do_action( 'post_submitbox_start' );
		echo '<div id="delete-action">';
		if ( current_user_can( 'delete_post', $post->ID ) ) {
			if ( !EMPTY_TRASH_DAYS )
				$delete_text = __( 'Delete Permanently' );
			else
				$delete_text = __( 'Move to Trash' );
			echo '<a class="submitdelete deletion" href="' . get_delete_post_link( $post->ID ) . '">' . $delete_text . '</a>';
		}
		echo '</div>';

		echo '<div id="publishing-action">';
		echo '<span class="spinner"></span>';
		if ( !in_array( $post->post_status, array( ' publish', 'future', 'private' ) ) || 0 == $post->ID ) {
			echo '<input name="original_publish" type="hidden" id="original_publish" value="' . esc_attr__( 'Publish' ) . '" />';
			submit_button( __( 'Save' ), 'primary button-large', 'publish', false );
		} else {
			echo '<input name="original_publish" type="hidden" id="original_publish" value="' . esc_attr__( 'Update' ) . '" />';
			echo '<input name="save" type="submit" class="button button-primary button-large" id="publish" value="' . esc_attr__( 'Update' ) . '" />';
		}
		echo '</div>';
		echo '<div class="clear"></div>';

		echo '</div>';
		echo '</div>';
	}

	public function edit_form_after_title ( $post ) {
		if ( $post->post_type !== 'xo_liteslider' || $post->post_status === 'auto-draft' ) {
			return;
		}
		?>
		<p class="description">
			<label for="xo-liteslider-shortcode"><?php _e( 'Copy this shortcode, please paste it into the post or page:', 'xo-liteslider' ); ?></label>
			<span class="shortcode wp-ui-primary">
				<input id="xo-liteslider-shortcode" onfocus="this.select();" readonly="readonly" class="large-text code" value="[xo_liteslider id=&quot;<?php echo $post->ID; ?>&quot;]" type="text">
			</span>
		</p>
		<?php
	}

	public function display_meta( $post ) {
		$slides = get_post_meta( $post->ID, 'slides', true );
		if ( empty( $slides ) ) {
			$slides = array( array(
				'image_id' => '',
				'title' => '',
				'alt' => '',
				'link' => '',
				'content' => '',
			) );
		}

		echo '<div id="xo-liteslider-slide">' . "\n";
		echo '<p>' . __( 'Drag the header of each item to change the order.', 'xo-liteslider' ) . '</p>';
		echo '<ul class="slide-repeat ui-sortable">' . "\n";
		$counter = 1;
		foreach ( $slides as $key => $value ) {
			$image_src = ( $value['image_id'] === '' ) ? false : wp_get_attachment_image_src( $value['image_id'], array( 150, 150 ) );
			echo '<li class="slide">';
				echo '<div class="slide-header">';
					echo '<span class="slide-header-title"></span>';
					echo '<span class="slide-header-button slide-header-append-button" title="' . __( 'Add Slide', 'xo-liteslider' ). '"></span>';
					echo '<span class="slide-header-button slide-header-remove-button" title="' . __( 'Delete Slide', 'xo-liteslider' ). '"></span>';
				echo '</div>';
				echo '<div class="slide-inner">';
					echo '<table class="slide-table"><tbody>';
						echo '<tr>';
							echo '<td style="width: 160px;">';
								echo '<div class="slide-image">';
									if ( $image_src ) {
										echo '<img src="' . $image_src[0] . '" />';
									}
								echo '</div>';
								echo '<input class="slide-image-id" name="xo_liteslider_slides[' . $counter . '][image_id]" type="hidden" value="' . $value['image_id'] . '"  /> ';
								echo '<span class="slide-image-button slide-image-setting" title="' . __( 'Select Image', 'xo-liteslider' ). '"></span>';
								echo '<span class="slide-image-button slide-image-clear" title="' . __( 'Clear Image', 'xo-liteslider' ). '"></span>';
							echo '</td>';
							echo '<td>';
								echo '<p>' . __( 'Title Text:', 'xo-liteslider' ). '<br /><input name="xo_liteslider_slides[' . $counter . '][title]" type="text" value="' . $value['title'] . '" /></p>';
								echo '<p>' . __( 'Alt Text:', 'xo-liteslider' ). '<br /><input name="xo_liteslider_slides[' . $counter . '][alt]" type="text" value="' . $value['alt'] . '" /></p>';
								echo '<p>' . __( 'Link (URL):', 'xo-liteslider' ). '<br /><input name="xo_liteslider_slides[' . $counter . '][link]" type="text" value="' . $value['link'] . '" /></p>';
								echo '<p>' . __( 'Content (HTML):', 'xo-liteslider' ). '<br /><textarea name="xo_liteslider_slides[' . $counter . '][content]" rows="3">' . $value['content'] . '</textarea></p>';
							echo '</td>';
						echo '</tr>';
					echo '</tbody></table>';
				echo '</div>';
			echo '</li>' . "\n";
			$counter++;
		}
		echo '</ul>' . "\n";
		echo '</div>' . "\n";

		wp_nonce_field( 'xo_liteslider_key', 'xo_liteslider_nonce' );

		// 日付セレクト コントロールの幅が狭くなる不具合(?)対策
		echo '<style type="text/css">.media-frame select.attachment-filters { min-width: 102px; }</style>';	
	}

	public function display_meta_parameter() {
		$parameters = get_post_meta( get_the_ID(), 'parameters', true );
		if ( empty( $parameters ) ) {
			$parameters = array( 'slideshow' => '1' );
		}

		$width = isset( $parameters['width'] ) ? $parameters['width'] : '';
		$height = isset( $parameters['height'] ) ? $parameters['height'] : '';
		$effect = isset( $parameters['effect'] ) ? $parameters['effect'] : 'slide';
		$navigation = isset( $parameters['navigation'] ) ? $parameters['navigation'] : '';
		$pagination = isset( $parameters['pagination'] ) ? $parameters['pagination'] : '';
		$slideshow = isset( $parameters['slideshow'] ) ? $parameters['slideshow'] : '';
		$slideshow_speed = !empty( $parameters['slideshow_speed'] ) ? $parameters['slideshow_speed'] : 5000;
		$animation_speed = !empty( $parameters['animation_speed'] ) ? $parameters['animation_speed'] : 1000;
		$content = isset( $parameters['content'] ) ? $parameters['content'] : '';
		$sort = isset( $parameters['sort'] ) ? $parameters['sort'] : 'asc';

		echo '<div id="xo-liteslider-parameter">' . "\n";
		echo '<table class="table-parameter"><tbody>';

		echo '<tr><th>' . __( 'Width:', 'xo-liteslider' ) . '</th><td><input name="xo_liteslider_parameters[width]" type="number" value="' . $width . '" class="small-text" min="0" step="1" /> px</td></tr>';
		echo '<tr><th>' . __( 'Height:', 'xo-liteslider' ) . '</th><td><input name="xo_liteslider_parameters[height]" type="number" value="' . $height . '" class="small-text" min="0" step="1" /> px</td></tr>';
		echo '<tr><th>' . __( 'Effect:', 'xo-liteslider' ) . '</th><td>';
		echo '<select name="xo_liteslider_parameters[effect]">';
		echo '<option value="slide"'. ( $effect == 'slide' ? ' selected' : '' ) . '>Slide</option>';
		echo '<option value="fade"'. ( $effect == 'fade' ? ' selected' : '' ) . '>Fade</option>';
		echo '<option value="slidefade"'. ( $effect == 'slidefade' ? ' selected' : '' ) . '>SlideFade</option>';
		echo '</select>';
		echo '</td></tr>';
		echo '<tr><th>' . __( 'Navigation:', 'xo-liteslider' ) . '</th><td><input id="xo_liteslider_parameters[navigation]" name="xo_liteslider_parameters[navigation]" type="checkbox" value="1" ' . checked( $navigation, '1', false ) .'/><label for="xo_liteslider_parameters[navigation]"></label></td></tr>';
		echo '<tr><th>' . __( 'Pagination:', 'xo-liteslider' ) . '</th><td><input id="xo_liteslider_parameters[pagination]" name="xo_liteslider_parameters[pagination]" type="checkbox" value="1" ' . checked( $pagination, '1', false ) .'/><label for="xo_liteslider_parameters[pagination]"></label></td></tr>';
		echo '<tr><th>' . __( 'Slideshow:', 'xo-liteslider' ) . '</th><td><input id="xo_liteslider_parameters[slideshow]" name="xo_liteslider_parameters[slideshow]" type="checkbox" value="1" ' . checked( $slideshow, '1', false ) .'/><label for="xo_liteslider_parameters[slideshow]"></label></td></tr>';
		echo '<tr><th>' . __( 'Delay:', 'xo-liteslider' ) . '</th><td><input name="xo_liteslider_parameters[slideshow_speed]" type="number" value="' . $slideshow_speed . '" class="small-text" min="0" step="100" /> ' . __( 'ms', 'xo-liteslider' ) . '</td></tr>';
		echo '<tr><th>' . __( 'Effect speed:', 'xo-liteslider' ) . '</th><td><input name="xo_liteslider_parameters[animation_speed]" type="number" value="' . $animation_speed . '" class="small-text" min="0" step="100" /> ' . __( 'ms', 'xo-liteslider' ) . '</td></tr>';
		echo '<tr><th>' . __( 'Content:', 'xo-liteslider' ) . '</th><td><input id="xo_liteslider_parameters[content]" name="xo_liteslider_parameters[content]" type="checkbox" value="1" ' . checked( $content, '1', false ) .'/><label for="xo_liteslider_parameters[content]"></label></td></tr>';
		echo '<tr><th>' . __( 'Order:', 'xo-liteslider' ) . '</th><td>';
		echo '<select name="xo_liteslider_parameters[sort]">';
		echo '<option value="asc"'. ( $sort == 'asc' ? ' selected' : '' ) . '>' . __( 'Ascending order', 'xo-liteslider' ) . '</option>';
		echo '<option value="desc"'. ( $sort == 'desc' ? ' selected' : '' ) . '>' . __( 'Descending order', 'xo-liteslider' ) . '</option>';
		echo '<option value="random"'. ( $sort == 'random' ? ' selected' : '' ) . '>' . __( 'Random', 'xo-liteslider' ) . '</option>';
		echo '</select>';
		echo '</td></tr>';
		echo '</tbody></table>';
		echo '<p class="howto">' . __( 'Width, height is optional. The default will be the image size.', 'xo-liteslider' ) . '</p>';
		echo '</div>' . "\n";
	}

	public function display_meta_usage( $post ) {
		echo '<div id="xo-liteslider-usage">' . "\n";
		echo '<p>' . __( 'Shortcode', 'xo-liteslider' );
		echo '<input id="xo-liteslider-usage-shortcode" onfocus="this.select();" readonly="readonly" class="large-text code" value="[xo_liteslider id=&quot;' . $post->ID . '&quot;]" type="text">';
		echo '</p>';
		echo '<p>' . __( 'Template tags', 'xo-liteslider' );
		echo '<input id="xo-liteslider-usage-template" onfocus="this.select();" readonly="readonly" class="large-text code" value="&lt;?php xo_liteslider(' . $post->ID . '); ?&gt;" type="text">';
		echo '</p>';
		echo '<p class="howto">' . __( 'Parameter default will be the oldest slider.', 'xo-liteslider' ) . '</p>';
		echo '</div>' . "\n";
	}
}
