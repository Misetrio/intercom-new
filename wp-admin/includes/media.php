<?php
/**
 * WordPress Administration Media API.
 *
 * @package WordPress
 * @subpackage Administration
 */

/**
 * Defines the default media upload tabs
 *
 * @since 2.5.0
 *
 * @return array default tabs
 */
function media_upload_tabs() {
	$_default_tabs = array(
		'type' => __('From Computer'), // handler action suffix => tab text
		'type_url' => __('From URL'),
		'gallery' => __('Gallery'),
		'library' => __('Media Library')
	);

	/**
	 * Filters the available tabs in the legacy (pre-3.5.0) media popup.
	 *
	 * @since 2.5.0
	 *
	 * @param array $_default_tabs An array of media tabs.
	 */
	return apply_filters( 'media_upload_tabs', $_default_tabs );
}

/**
 * Adds the gallery tab back to the tabs array if post has image attachments
 *
 * @since 2.5.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param array $tabs
 * @return array $tabs with gallery if post has image attachment
 */
function update_gallery_tab($tabs) {
	global $wpdb;

	if ( !isset($_REQUEST['post_id']) ) {
		unset($tabs['gallery']);
		return $tabs;
	}

	$post_id = intval($_REQUEST['post_id']);

	if ( $post_id )
		$attachments = intval( $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_status != 'trash' AND post_parent = %d", $post_id ) ) );

	if ( empty($attachments) ) {
		unset($tabs['gallery']);
		return $tabs;
	}

	$tabs['gallery'] = sprintf(__('Gallery (%s)'), "<span id='attachments-count'>$attachments</span>");

	return $tabs;
}

/**
 * Outputs the legacy media upload tabs UI.
 *
 * @since 2.5.0
 *
 * @global string $redir_tab
 */
function the_media_upload_tabs() {
	global $redir_tab;
	$tabs = media_upload_tabs();
	$default = 'type';

	if ( !empty($tabs) ) {
		echo "<ul id='sidemenu'>\n";
		if ( isset($redir_tab) && array_key_exists($redir_tab, $tabs) ) {
			$current = $redir_tab;
		} elseif ( isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs) ) {
			$current = $_GET['tab'];
		} else {
			/** This filter is documented in wp-admin/media-upload.php */
			$current = apply_filters( 'media_upload_default_tab', $default );
		}

		foreach ( $tabs as $callback => $text ) {
			$class = '';

			if ( $current == $callback )
				$class = " class='current'";

			$href = add_query_arg(array('tab' => $callback, 's' => false, 'paged' => false, 'post_mime_type' => false, 'm' => false));
			$link = "<a href='" . esc_url($href) . "'$class>$text</a>";
			echo "\t<li id='" . esc_attr("tab-$callback") . "'>$link</li>\n";
		}
		echo "</ul>\n";
	}
}

/**
 * Retrieves the image HTML to send to the editor.
 *
 * @since 2.5.0
 *
 * @param int          $id      Image attachment id.
 * @param string       $caption Image caption.
 * @param string       $title   Image title attribute.
 * @param string       $align   Image CSS alignment property.
 * @param string       $url     Optional. Image src URL. Default empty.
 * @param bool|string  $rel     Optional. Value for rel attribute or whether to add a default value. Default false.
 * @param string|array $size    Optional. Image size. Accepts any valid image size, or an array of width
 *                              and height values in pixels (in that order). Default 'medium'.
 * @param string       $alt     Optional. Image alt attribute. Default empty.
 * @return string The HTML output to insert into the editor.
 */
function get_image_send_to_editor( $id, $caption, $title, $align, $url = '', $rel = false, $size = 'medium', $alt = '' ) {

	$html = get_image_tag( $id, $alt, '', $align, $size );

	if ( $rel ) {
		if ( is_string( $rel ) ) {
			$rel = ' rel="' . esc_attr( $rel ) . '"';
		} else {
			$rel = ' rel="attachment wp-att-' . intval( $id ) . '"';
		}
	} else {
		$rel = '';
	}

	if ( $url )
		$html = '<a href="' . esc_attr( $url ) . '"' . $rel . '>' . $html . '</a>';

	/**
	 * Filters the image HTML markup to send to the editor when inserting an image.
	 *
	 * @since 2.5.0
	 *
	 * @param string       $html    The image HTML markup to send.
	 * @param int          $id      The attachment id.
	 * @param string       $caption The image caption.
	 * @param string       $title   The image title.
	 * @param string       $align   The image alignment.
	 * @param string       $url     The image source URL.
	 * @param string|array $size    Size of image. Image size or array of width and height values
	 *                              (in that order). Default 'medium'.
	 * @param string       $alt     The image alternative, or alt, text.
	 */
	$html = apply_filters( 'image_send_to_editor', $html, $id, $caption, $title, $align, $url, $size, $alt );

	return $html;
}

/**
 * Adds image shortcode with caption to editor
 *
 * @since 2.6.0
 *
 * @param string $html
 * @param integer $id
 * @param string $caption image caption
 * @param string $title image title attribute
 * @param string $align image css alignment property
 * @param string $url image src url
 * @param string $size image size (thumbnail, medium, large, full or added with add_image_size() )
 * @param string $alt image alt attribute
 * @return string
 */
function image_add_caption( $html, $id, $caption, $title, $align, $url, $size, $alt = '' ) {

	/**
	 * Filters the caption text.
	 *
	 * Note: If the caption text is empty, the caption shortcode will not be appended
	 * to the image HTML when inserted into the editor.
	 *
	 * Passing an empty value also prevents the {@see 'image_add_caption_shortcode'}
	 * Filters from being evaluated at the end of image_add_caption().
	 *
	 * @since 4.1.0
	 *
	 * @param string $caption The original caption text.
	 * @param int    $id      The attachment ID.
	 */
	$caption = apply_filters( 'image_add_caption_text', $caption, $id );

	/**
	 * Filters whether to disable captions.
	 *
	 * Prevents image captions from being appended to image HTML when inserted into the editor.
	 *
	 * @since 2.6.0
	 *
	 * @param bool $bool Whether to disable appending captions. Returning true to the filter
	 *                   will disable captions. Default empty string.
	 */
	if ( empty($caption) || apply_filters( 'disable_captions', '' ) )
		return $html;

	$id = ( 0 < (int) $id ) ? 'attachment_' . $id : '';

	if ( ! preg_match( '/width=["\']([0-9]+)/', $html, $matches ) )
		return $html;

	$width = $matches[1];

	$caption = str_replace( array("\r\n", "\r"), "\n", $caption);
	$caption = preg_replace_callback( '/<[a-zA-Z0-9]+(?: [^<>]+>)*/', '_cleanup_image_add_caption', $caption );

	// Convert any remaining line breaks to <br>.
	$caption = preg_replace( '/[ \n\t]*\n[ \t]*/', '<br />', $caption );

	$html = preg_replace( '/(class=["\'][^\'"]*)align(none|left|right|center)\s?/', '$1', $html );
	if ( empty($align) )
		$align = 'none';

	$shcode = '[caption id="' . $id . '" align="align' . $align	. '" width="' . $width . '"]' . $html . ' ' . $caption . '[/caption]';

	/**
	 * Filters the image HTML markup including the caption shortcode.
	 *
	 * @since 2.6.0
	 *
	 * @param string $shcode The image HTML markup with caption shortcode.
	 * @param string $html   The image HTML markup.
	 */
	return apply_filters( 'image_add_caption_shortcode', $shcode, $html );
}

/**
 * Private preg_replace callback used in image_add_caption()
 *
 * @access private
 * @since 3.4.0
 */
function _cleanup_image_add_caption( $matches ) {
	// Remove any line breaks from inside the tags.
	return preg_replace( '/[\r\n\t]+/', ' ', $matches[0] );
}

/**
 * Adds image html to editor
 *
 * @since 2.5.0
 *
 * @param string $html
 */
function media_send_to_editor($html) {
?>
<script type="text/javascript">
var win = window.dialogArguments || opener || parent || top;
win.send_to_editor( <?php echo wp_json_encode( $html ); ?> );
</script>
<?php
	exit;
}

/**
 * Save a file submitted from a POST request and create an attachment post for it.
 *
 * @since 2.5.0
 *
 * @param string $file_id   Index of the `$_FILES` array that the file was sent. Required.
 * @param int    $post_id   The post ID of a post to attach the media item to. Required, but can
 *                          be set to 0, creating a media item that has no relationship to a post.
 * @param array  $post_data Overwrite some of the attachment. Optional.
 * @param array  $overrides Override the wp_handle_upload() behavior. Optional.
 * @return int|WP_Error ID of the attachment or a WP_Error object on failure.
 */
function media_handle_upload($file_id, $post_id, $post_data = array(), $overrides = array( 'test_form' => false )) {

	$time = current_time('mysql');
	if ( $post = get_post($post_id) ) {
		// The post date doesn't usually matter for pages, so don't backdate this upload.
		if ( 'page' !== $post->post_type && substr( $post->post_date, 0, 4 ) > 0 )
			$time = $post->post_date;
	}

	$file = wp_handle_upload($_FILES[$file_id], $overrides, $time);

	if ( isset($file['error']) )
		return new WP_Error( 'upload_error', $file['error'] );

	$name = $_FILES[$file_id]['name'];
	$ext  = pathinfo( $name, PATHINFO_EXTENSION );
	$name = wp_basename( $name, ".$ext" );

	$url = $file['url'];
	$type = $file['type'];
	$file = $file['file'];
	$title = sanitize_text_field( $name );
	$content = '';
	$excerpt = '';

	if ( preg_match( '#^audio#', $type ) ) {
		$meta = wp_read_audio_metadata( $file );

		if ( ! empty( $meta['title'] ) ) {
			$title = $meta['title'];
		}

		if ( ! empty( $title ) ) {

			if ( ! empty( $meta['album'] ) && ! empty( $meta['artist'] ) ) {
				/* translators: 1: audio track title, 2: album title, 3: artist name */
				$content .= sprintf( __( '"%1$s" from %2$s by %3$s.' ), $title, $meta['album'], $meta['artist'] );
			} elseif ( ! empty( $meta['album'] ) ) {
				/* translators: 1: audio track title, 2: album title */
				$content .= sprintf( __( '"%1$s" from %2$s.' ), $title, $meta['album'] );
			} elseif ( ! empty( $meta['artist'] ) ) {
				/* translators: 1: audio track title, 2: artist name */
				$content .= sprintf( __( '"%1$s" by %2$s.' ), $title, $meta['artist'] );
			} else {
				/* translators: 1: audio track title */
				$content .= sprintf( __( '"%s".' ), $title );
			}

		} elseif ( ! empty( $meta['album'] ) ) {

			if ( ! empty( $meta['artist'] ) ) {
				/* translators: 1: audio album title, 2: artist name */
				$content .= sprintf( __( '%1$s by %2$s.' ), $meta['album'], $meta['artist'] );
			} else {
				$content .= $meta['album'] . '.';
			}

		} elseif ( ! empty( $meta['artist'] ) ) {

			$content .= $meta['artist'] . '.';

		}

		if ( ! empty( $meta['year'] ) ) {
			/* translators: Audio file track information. 1: Year of audio track release */
			$content .= ' ' . sprintf( __( 'Released: %d.' ), $meta['year'] );
		}

		if ( ! empty( $meta['track_number'] ) ) {
			$track_number = explode( '/', $meta['track_number'] );
			if ( isset( $track_number[1] ) ) {
				/* translators: Audio file track information. 1: Audio track number, 2: Total audio tracks */
				$content .= ' ' . sprintf( __( 'Track %1$s of %2$s.' ), number_format_i18n( $track_number[0] ), number_format_i18n( $track_number[1] ) );
			} else {
				/* translators: Audio file track information. 1: Audio track number */
				$content .= ' ' . sprintf( __( 'Track %1$s.' ), number_format_i18n( $track_number[0] ) );
			}
		}

		if ( ! empty( $meta['genre'] ) ) {
			/* translators: Audio file genre information. 1: Audio genre name */
			$content .= ' ' . sprintf( __( 'Genre: %s.' ), $meta['genre'] );
		}

	// Use image exif/iptc data for title and caption defaults if possible.
	} elseif ( 0 === strpos( $type, 'image/' ) && $image_meta = wp_read_image_metadata( $file ) ) {
		if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
			$title = $image_meta['title'];
		}

		if ( trim( $image_meta['caption'] ) ) {
			$excerpt = $image_meta['caption'];
		}
	}

	// Construct the attachment array
	$attachment = array_merge( array(
		'post_mime_type' => $type,
		'guid' => $url,
		'post_parent' => $post_id,
		'post_title' => $title,
		'post_content' => $content,
		'post_excerpt' => $excerpt,
	), $post_data );

	// This should never be set as it would then overwrite an existing attachment.
	unset( $attachment['ID'] );

	// Save the data
	$id = wp_insert_attachment( $attachment, $file, $post_id, true );
	if ( !is_wp_error($id) ) {
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
	}

	return $id;

}

/**
 * Handles a side-loaded file in the same way as an uploaded file is handled by media_handle_upload().
 *
 * @since 2.6.0
 *
 * @param array  $file_array Array similar to a `$_FILES` upload array.
 * @param int    $post_id    The post ID the media is associated with.
 * @param string $desc       Optional. Description of the side-loaded file. Default null.
 * @param array  $post_data  Optional. Post data to override. Default empty array.
 * @return int|object The ID of the attachment or a WP_Error on failure.
 */
function media_handle_sideload( $file_array, $post_id, $desc = null, $post_data = array() ) {
	$overrides = array('test_form'=>false);

	$time = current_time( 'mysql' );
	if ( $post = get_post( $post_id ) ) {
		if ( substr( $post->post_date, 0, 4 ) > 0 )
			$time = $post->post_date;
	}

	$file = wp_handle_sideload( $file_array, $overrides, $time );
	if ( isset($file['error']) )
		return new WP_Error( 'upload_error', $file['error'] );

	$url = $file['url'];
	$type = $file['type'];
	$file = $file['file'];
	$title = preg_replace('/\.[^.]+$/', '', basename($file));
	$content = '';

	// Use image exif/iptc data for title and caption defaults if possible.
	if ( $image_meta = wp_read_image_metadata( $file ) ) {
		if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) )
			$title = $image_meta['title'];
		if ( trim( $image_meta['caption'] ) )
			$content = $image_meta['caption'];
	}

	if ( isset( $desc ) )
		$title = $desc;

	// Construct the attachment array.
	$attachment = array_merge( array(
		'post_mime_type' => $type,
		'guid' => $url,
		'post_parent' => $post_id,
		'post_title' => $title,
		'post_content' => $content,
	), $post_data );

	// This should never be set as it would then overwrite an existing attachment.
	unset( $attachment['ID'] );

	// Save the attachment metadata
	$id = wp_insert_attachment($attachment, $file, $post_id);
	if ( !is_wp_error($id) )
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );

	return $id;
}

/**
 * Adds the iframe to display content for the media upload page
 *
 * @since 2.5.0
 *
 * @global int $body_id
 *
 * @param string|callable $content_func
 */
function wp_iframe($content_func /* ... */) {
	_wp_admin_html_begin();
?>
<title><?php bloginfo('name') ?> &rsaquo; <?php _e('Uploads'); ?> &#8212; <?php _e('WordPress'); ?></title>
<?php

wp_enqueue_style( 'colors' );
// Check callback name for 'media'
if ( ( is_array( $content_func ) && ! empty( $content_func[1] ) && 0 === strpos( (string) $content_func[1], 'media' ) )
	|| ( ! is_array( $content_func ) && 0 === strpos( $content_func, 'media' ) ) )
	wp_enqueue_style( 'deprecated-media' );
wp_enqueue_style( 'ie' );
?>
<script type="text/javascript">
addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>', pagenow = 'media-upload-popup', adminpage = 'media-upload-popup',
isRtl = <?php echo (int) is_rtl(); ?>;
</script>
<?php
	/** This action is documented in wp-admin/admin-header.php */
	do_action( 'admin_enqueue_scripts', 'media-upload-popup' );

	/**
	 * Fires when admin styles enqueued for the legacy (pre-3.5.0) media upload popup are printed.
	 *
	 * @since 2.9.0
	 */
	do_action( 'admin_print_styles-media-upload-popup' );

	/** This action is documented in wp-admin/admin-header.php */
	do_action( 'admin_print_styles' );

	/**
	 * Fires when admin scripts enqueued for the legacy (pre-3.5.0) media upload popup are printed.
	 *
	 * @since 2.9.0
	 */
	do_action( 'admin_print_scripts-media-upload-popup' );

	/** This action is documented in wp-admin/admin-header.php */
	do_action( 'admin_print_scripts' );

	/**
	 * Fires when scripts enqueued for the admin header for the legacy (pre-3.5.0)
	 * media upload popup are printed.
	 *
	 * @since 2.9.0
	 */
	do_action( 'admin_head-media-upload-popup' );

	/** This action is documented in wp-admin/admin-header.php */
	do_action( 'admin_head' );

if ( is_string( $content_func ) ) {
	/**
	 * Fires in the admin header for each specific form tab in the legacy
	 * (pre-3.5.0) media upload popup.
	 *
	 * The dynamic portion of the hook, `$content_func`, refers to the form
	 * callback for the media upload type. Possible values include
	 * 'media_upload_type_form', 'media_upload_type_url_form', and
	 * 'media_upload_library_form'.
	 *
	 * @since 2.5.0
	 */
	do_action( "admin_head_{$content_func}" );
}
?>
</head>
<body<?php if ( isset($GLOBALS['body_id']) ) echo ' id="' . $GLOBALS['body_id'] . '"'; ?> class="wp-core-ui no-js">
<script type="text/javascript">
document.body.className = document.body.className.replace('no-js', 'js');
</script>
<?php
	$args = func_get_args();
	$args = array_slice($args, 1);
	call_user_func_array($content_func, $args);

	/** This action is documented in wp-admin/admin-footer.php */
	do_action( 'admin_print_footer_scripts' );
?>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
</body>
</html>
<?php
}

/**
 * Adds the media button to the editor
 *
 * @since 2.5.0
 *
 * @global int $post_ID
 *
 * @staticvar int $instance
 *
 * @param string $editor_id
 */
function media_buttons($editor_id = 'content') {
	static $instance = 0;
	$instance++;

	$post = get_post();
	if ( ! $post && ! empty( $GLOBALS['post_ID'] ) )
		$post = $GLOBALS['post_ID'];

	wp_enqueue_media( array(
		'post' => $post
	) );

	$img = '<span class="wp-media-buttons-icon"></span> ';

	$id_attribute = $instance === 1 ? ' id="insert-media-button"' : '';
	printf( '<button type="button"%s class="button insert-media add_media" data-editor="%s">%s</button>',
		$id_attribute,
		esc_attr( $editor_id ),
		$img . __( 'Add Media' )
	);
	/**
	 * Filters the legacy (pre-3.5.0) media buttons.
	 *
	 * Use {@see 'media_buttons'} action instead.
	 *
	 * @since 2.5.0
	 * @deprecated 3.5.0 Use {@see 'media_buttons'} action instead.
	 *
	 * @param string $string Media buttons context. Default empty.
	 */
	$legacy_filter = apply_filters( 'media_buttons_context', '' );

	if ( $legacy_filter ) {
		// #WP22559. Close <a> if a plugin started by closing <a> to open their own <a> tag.
		if ( 0 === stripos( trim( $legacy_filter ), '</a>' ) )
			$legacy_filter .= '</a>';
		echo $legacy_filter;
	}
}

/**
 *
 * @global int $post_ID
 * @param string $type
 * @param int $post_id
 * @param string $tab
 * @return string
 */
function get_upload_iframe_src( $type = null, $post_id = null, $tab = null ) {
	global $post_ID;

	if ( empty( $post_id ) )
		$post_id = $post_ID;

	$upload_iframe_src = add_query_arg( 'post_id', (int) $post_id, admin_url('media-upload.php') );

	if ( $type && 'media' != $type )
		$upload_iframe_src = add_query_arg('type', $type, $upload_iframe_src);

	if ( ! empty( $tab ) )
		$upload_iframe_src = add_query_arg('tab', $tab, $upload_iframe_src);

	/**
	 * Filters the upload iframe source URL for a specific media type.
	 *
	 * The dynamic portion of the hook name, `$type`, refers to the type
	 * of media uploaded.
	 *
	 * @since 3.0.0
	 *
	 * @param string $upload_iframe_src The upload iframe source URL by type.
	 */
	$upload_iframe_src = apply_filters( "{$type}_upload_iframe_src", $upload_iframe_src );

	return add_query_arg('TB_iframe', true, $upload_iframe_src);
}

/**
 * Handles form submissions for the legacy media uploader.
 *
 * @since 2.5.0
 *
 * @return mixed void|object WP_Error on failure
 */
function media_upload_form_handler() {
	check_admin_referer('media-form');

	$errors = null;

	if ( isset($_POST['send']) ) {
		$keys = array_keys( $_POST['send'] );
		$send_id = (int) reset( $keys );
	}

	if ( !empty($_POST['attachments']) ) foreach ( $_POST['attachments'] as $attachment_id => $attachment ) {
		$post = $_post = get_post($attachment_id, ARRAY_A);

		if ( !current_user_can( 'edit_post', $attachment_id ) )
			continue;

		if ( isset($attachment['post_content']) )
			$post['post_content'] = $attachment['post_content'];
		if ( isset($attachment['post_title']) )
			$post['post_title'] = $attachment['post_title'];
		if ( isset($attachment['post_excerpt']) )
			$post['post_excerpt'] = $attachment['post_excerpt'];
		if ( isset($attachment['menu_order']) )
			$post['menu_order'] = $attachment['menu_order'];

		if ( isset($send_id) && $attachment_id == $send_id ) {
			if ( isset($attachment['post_parent']) )
				$post['post_parent'] = $attachment['post_parent'];
		}

		/**
		 * Filters the attachment fields to be saved.
		 *
		 * @since 2.5.0
		 *
		 * @see wp_get_attachment_metadata()
		 *
		 * @param array $post       An array of post data.
		 * @param array $attachment An array of attachment metadata.
		 */
		$post = apply_filters( 'attachment_fields_to_save', $post, $attachment );

		if ( isset($attachment['image_alt']) ) {
			$image_alt = wp_unslash( $attachment['image_alt'] );
			if ( $image_alt != get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ) {
				$image_alt = wp_strip_all_tags( $image_alt, true );

				// Update_meta expects slashed.
				update_post_meta( $attachment_id, '_wp_attachment_image_alt', wp_slash( $image_alt ) );
			}
		}

		if ( isset($post['errors']) ) {
			$errors[$attachment_id] = $post['errors'];
			unset($post['errors']);
		}

		if ( $post != $_post )
			wp_update_post($post);

		foreach ( get_attachment_taxonomies($post) as $t ) {
			if ( isset($attachment[$t]) )
				wp_set_object_terms($attachment_id, array_map('trim', preg_split('/,+/', $attachment[$t])), $t, false);
		}
	}

	if ( isset($_POST['insert-gallery']) || isset($_POST['update-gallery']) ) { ?>
		<script type="text/javascript">
		var win = window.dialogArguments || opener || parent || top;
		win.tb_remove();
		</script>
		<?php
		exit;
	}

	if ( isset($send_id) ) {
		$attachment = wp_unslash( $_POST['attachments'][$send_id] );

		$html = isset( $attachment['post_title'] ) ? $attachment['post_title'] : '';
		if ( !empty($attachment['url']) ) {
			$rel = '';
			if ( strpos($attachment['url'], 'attachment_id') || get_attachment_link($send_id) == $attachment['url'] )
				$rel = " rel='attachment wp-att-" . esc_attr($send_id) . "'";
			$html = "<a href='{$attachment['url']}'$rel>$html</a>";
		}

		/**
		 * Filters the HTML markup for a media item sent to the editor.
		 *
		 * @since 2.5.0
		 *
		 * @see wp_get_attachment_metadata()
		 *
		 * @param string $html       HTML markup for a media item sent to the editor.
		 * @param int    $send_id    The first key from the $_POST['send'] data.
		 * @param array  $attachment Array of attachment metadata.
		 */
		$html = apply_filters( 'media_send_to_editor', $html, $send_id, $attachment );
		return media_send_to_editor($html);
	}

	return $errors;
}

/**
 * Handles the process of uploading media.
 *
 * @since 2.5.0
 *
 * @return null|string
 */
function wp_media_upload_handler() {
	$errors = array();
	$id = 0;

	if ( isset($_POST['html-upload']) && !empty($_FILES) ) {
		check_admin_referer('media-form');
		// Upload File button was clicked
		$id = media_handle_upload('async-upload', $_REQUEST['post_id']);
		unset($_FILES);
		if ( is_wp_error($id) ) {
			$errors['upload_error'] = $id;
			$id = false;
		}
	}

	if ( !empty($_POST['insertonlybutton']) ) {
		$src = $_POST['src'];
		if ( !empty($src) && !strpos($src, '://') )
			$src = "http://$src";

		if ( isset( $_POST['media_type'] ) && 'image' != $_POST['media_type'] ) {
			$title = esc_html( wp_unslash( $_POST['title'] ) );
			if ( empty( $title ) )
				$title = esc_html( basename( $src ) );

			if ( $title && $src )
				$html = "<a href='" . esc_url($src) . "'>$title</a>";

			$type = 'file';
			if ( ( $ext = preg_replace( '/^.+?\.([^.]+)$/', '$1', $src ) ) && ( $ext_type = wp_ext2type( $ext ) )
				&& ( 'audio' == $ext_type || 'video' == $ext_type ) )
					$type = $ext_type;

			/**
			 * Filters the URL sent to the editor for a specific media type.
			 *
			 * The dynamic portion of the hook name, `$type`, refers to the type
			 * of media being sent.
			 *
			 * @since 3.3.0
			 *
			 * @param string $html  HTML markup sent to the editor.
			 * @param string $src   Media source URL.
			 * @param string $title Media title.
			 */
			$html = apply_filters( "{$type}_send_to_editor_url", $html, esc_url_raw( $src ), $title );
		} else {
			$align = '';
			$alt = esc_attr( wp_unslash( $_POST['alt'] ) );
			if ( isset($_POST['align']) ) {
				$align = esc_attr( wp_unslash( $_POST['align'] ) );
				$class = " class='align$align'";
			}
			if ( !empty($src) )
				$html = "<img src='" . esc_url($src) . "' alt='$alt'$class />";

			/**
			 * Filters the image URL sent to the editor.
			 *
			 * @since 2.8.0
			 *
			 * @param string $html  HTML markup sent to the editor for an image.
			 * @param string $src   Image source URL.
			 * @param string $alt   Image alternate, or alt, text.
			 * @param string $align The image alignment. Default 'alignnone'. Possible values include
			 *                      'alignleft', 'aligncenter', 'alignright', 'alignnone'.
			 */
			$html = apply_filters( 'image_send_to_editor_url', $html, esc_url_raw( $src ), $alt, $align );
		}

		return media_send_to_editor($html);
	}

	if ( isset( $_POST['save'] ) ) {
		$errors['upload_notice'] = __('Saved.');
		wp_enqueue_script( 'admin-gallery' );
 		return wp_iframe( 'media_upload_gallery_form', $errors );

	} elseif ( ! empty( $_POST ) ) {
		$return = media_upload_form_handler();

		if ( is_string($return) )
			return $return;
		if ( is_array($return) )
			$errors = $return;
	}

	if ( isset($_GET['tab']) && $_GET['tab'] == 'type_url' ) {
		$type = 'image';
		if ( isset( $_GET['type'] ) && in_array( $_GET['type'], array( 'video', 'audio', 'file' ) ) )
			$type = $_GET['type'];
		return wp_iframe( 'media_upload_type_url_form', $type, $errors, $id );
	}

	return wp_iframe( 'media_upload_type_form', 'image', $errors, $id );
}

/**
 * Downloads an image from the specified URL and attaches it to a post.
 *
 * @since 2.6.0
 * @since 4.2.0 Introduced the `$return` parameter.
 * @since 4.8.0 Introduced the 'id' option within the `$return` parameter.
 *
 * @param string $file    The URL of the image to download.
 * @param int    $post_id The post ID the media is to be associated with.
 * @param string $desc    Optional. Description of the image.
 * @param string $return  Optional. Accepts 'html' (image tag html) or 'src' (URL), or 'id' (attachment ID). Default 'html'.
 * @return string|WP_Error Populated HTML img tag on success, WP_Error object otherwise.
 */
function media_sideload_image( $file, $post_id, $desc = null, $return = 'html' ) {
	if ( ! empty( $file ) ) {

		// Set variables for storage, fix file filename for query strings.
		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
		if ( ! $matches ) {
			return new WP_Error( 'image_sideload_failed', __( 'Invalid image URL' ) );
		}

		$file_array = array();
		$file_array['name'] = basename( $matches[0] );

		// Download file to temp location.
		$file_array['tmp_name'] = download_url( $file );

		// If error storing temporarily, return the error.
		if ( is_wp_error( $file_array['tmp_name'] ) ) {
			return $file_array['tmp_name'];
		}

		// Do the validation and storage stuff.
		$id = media_handle_sideload( $file_array, $post_id, $desc );

		// If error storing permanently, unlink.
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );
			return $id;
		// If attachment id was requested, return it early.
		} elseif ( $return === 'id' ) {
			return $id;
		}

		$src = wp_get_attachment_url( $id );
	}

	// Finally, check to make sure the file has been saved, then return the HTML.
	if ( ! empty( $src ) ) {
		if ( $return === 'src' ) {
			return $src;
		}

		$alt = isset( $desc ) ? esc_attr( $desc ) : '';
		$html = "<img src='$src' alt='$alt' />";
		return $html;
	} else {
		return new WP_Error( 'image_sideload_failed' );
	}
}

/**
 * Retrieves the legacy media uploader form in an iframe.
 *
 * @since 2.5.0
 *
 * @return string|null
 */
function media_upload_gallery() {
	$errors = array();

	if ( !empty($_POST) ) {
		$return = media_upload_form_handler();

		if ( is_string($return) )
			return $return;
		if ( is_array($return) )
			$errors = $return;
	}

	wp_enqueue_script('admin-gallery');
	return wp_iframe( 'media_upload_gallery_form', $errors );
}

/**
 * Retrieves the legacy media library form in an iframe.
 *
 * @since 2.5.0
 *
 * @return string|null
 */
function media_upload_library() {
	$errors = array();
	if ( !empty($_POST) ) {
		$return = media_upload_form_handler();

		if ( is_string($return) )
			return $return;
		if ( is_array($return) )
			$errors = $return;
	}

	return wp_iframe( 'media_upload_library_form', $errors );
}

/**
 * Retrieve HTML for the image alignment radio buttons with the specified one checked.
 *
 * @since 2.7.0
 *
 * @param WP_Post $post
 * @param string $checked
 * @return string
 */
function image_align_input_fields( $post, $checked = '' ) {

	if ( empty($checked) )
		$checked = get_user_setting('align', 'none');

	$alignments = array('none' => __('None'), 'left' => __('Left'), 'center' => __('Center'), 'right' => __('Right'));
	if ( !array_key_exists( (string) $checked, $alignments ) )
		$checked = 'none';

	$out = array();
	foreach ( $alignments as $name => $label ) {
		$name = esc_attr($name);
		$out[] = "<input type='radio' name='attachments[{$post->ID}][align]' id='image-align-{$name}-{$post->ID}' value='$name'".
			( $checked == $name ? " checked='checked'" : "" ) .
			" /><label for='image-align-{$name}-{$post->ID}' class='align image-align-{$name}-label'>$label</label>";
	}
	return join("\n", $out);
}

/**
 * Retrieve HTML for the size radio buttons with the specified one checked.
 *
 * @since 2.7.0
 *
 * @param WP_Post $post
 * @param bool|string $check
 * @return array
 */
function image_size_input_fields( $post, $check = '' ) {
	/**
	 * Filters the names and labels of the default image sizes.
	 *
	 * @since 3.3.0
	 *
	 * @param array $size_names Array of image sizes and their names. Default values
	 *                          include 'Thumbnail', 'Medium', 'Large', 'Full Size'.
	 */
	$size_names = apply_filters( 'image_size_names_choose', array(
		'thumbnail' => __( 'Thumbnail' ),
		'medium'    => __( 'Medium' ),
		'large'     => __( 'Large' ),
		'full'      => __( 'Full Size' )
	) );

	if ( empty( $check ) ) {
		$check = get_user_setting('imgsize', 'medium');
	}
	$out = array();

	foreach ( $size_names as $size => $label ) {
		$downsize = image_downsize( $post->ID, $size );
		$checked = '';

		// Is this size selectable?
		$enabled = ( $downsize[3] || 'full' == $size );
		$css_id = "image-size-{$size}-{$post->ID}";

		// If this size is the default but that's not available, don't select it.
		if ( $size == $check ) {
			if ( $enabled ) {
				$checked = " checked='checked'";
			} else {
				$check = '';
			}
		} elseif ( ! $check && $enabled && 'thumbnail' != $size ) {
			/*
			 * If $check is not enabled, default to the first available size
			 * that's bigger than a thumbnail.
			 */
			$check = $size;
			$checked = " checked='checked'";
		}

		$html = "<div class='image-size-item'><input type='radio' " . disabled( $enabled, false, false ) . "name='attachments[$post->ID][image-size]' id='{$css_id}' value='{$size}'$checked />";

		$html .= "<label for='{$css_id}'>$label</label>";

		// Only show the dimensions if that choice is available.
		if ( $enabled ) {
			$html .= " <label for='{$css_id}' class='help'>" . sprintf( "(%d&nbsp;&times;&nbsp;%d)", $downsize[1], $downsize[2] ). "</label>";
		}
		$html .= '</div>';

		$out[] = $html;
	}

	return array(
		'label' => __( 'Size' ),
		'input' => 'html',
		'html'  => join( "\n", $out ),
	);
}

/**
 * Retrieve HTML for the Link URL buttons with the default link type as specified.
 *
 * @since 2.7.0
 *
 * @param WP_Post $post
 * @param string $url_type
 * @return string
 */
function image_link_input_fields($post, $url_type = '') {

	$file = wp_get_attachment_url($post->ID);
	$link = get_attachment_link($post->ID);

	if ( empty($url_type) )
		$url_type = get_user_setting('urlbutton', 'post');

	$url = '';
	if ( $url_type == 'file' )
		$url = $file;
	elseif ( $url_type == 'post' )
		$url = $link;

	return "
	<input type='text' class='text urlfield' name='attachments[$post->ID][url]' value='" . esc_attr($url) . "' /><br />
	<button type='button' class='button urlnone' data-link-url=''>" . __('None') . "</button>
	<button type='button' class='button urlfile' data-link-url='" . esc_attr($file) . "'>" . __('File URL') . "</button>
	<button type='button' class='button urlpost' data-link-url='" . esc_attr($link) . "'>" . __('Attachment Post URL') . "</button>
";
}

/**
 * Output a textarea element for inputting an attachment caption.
 *
 * @since 3.4.0
 *
 * @param WP_Post $edit_post Attachment WP_Post object.
 * @return string HTML markup for the textarea element.
 */
function wp_caption_input_textarea($edit_post) {
	// Post data is already escaped.
	$name = "attachments[{$edit_post->ID}][post_excerpt]";

	return '<textarea name="' . $name . '" id="' . $name . '">' . $edit_post->post_excerpt . '</textarea>';
}

/**
 * Retrieves the image attachment fields to edit form fields.
 *
 * @since 2.5.0
 *
 * @param array $form_fields
 * @param object $post
 * @return array
 */
function image_attachment_fields_to_edit($form_fields, $post) {
	return $form_fields;
}

/**
 * Retrieves the single non-image attachment fields to edit form fields.
 *
 * @since 2.5.0
 *
 * @param array   $form_fields An array of attachment form fields.
 * @param WP_Post $post        The WP_Post attachment object.
 * @return array Filtered attachment form fields.
 */
function media_single_attachment_fields_to_edit( $form_fields, $post ) {
	unset($form_fields['url'], $form_fields['align'], $form_fields['image-size']);
	return $form_fields;
}

/**
 * Retrieves the post non-image attachment fields to edito form fields.
 *
 * @since 2.8.0
 *
 * @param array   $form_fields An array of attachment form fields.
 * @param WP_Post $post        The WP_Post attachment object.
 * @return array Filtered attachment form fields.
 */
function media_post_single_attachment_fields_to_edit( $form_fields, $post ) {
	unset($form_fields['image_url']);
	return $form_fields;
}

/**
 * Filters input from media_upload_form_handler() and assigns a default
 * post_title from the file name if none supplied.
 *
 * Illustrates the use of the {@see 'attachment_fields_to_save'} filter
 * which can be used to add default values to any field before saving to DB.
 *
 * @since 2.5.0
 *
 * @param array $post       The WP_Post attachment object converted to an array.
 * @param array $attachment An array of attachment metadata.
 * @return array Filtered attachment post object.
 */
function image_attachment_fields_to_save( $post, $attachment ) {
	if ( substr( $post['post_mime_type'], 0, 5 ) == 'image' ) {
		if ( strlen( trim( $post['post_title'] ) ) == 0 ) {
			$attachment_url = ( isset( $post['attachment_url'] ) ) ? $post['attachment_url'] : $post['guid'];
			$post['post_title'] = preg_replace( '/\.\w+$/', '', wp_basename( $attachment_url ) );
			$post['errors']['post_title']['errors'][] = __( 'Empty Title filled from filename.' );
		}
	}

	return $post;
}

/**
 * Retrieves the media element HTML to send to the editor.
 *
 * @since 2.5.0
 *
 * @param string $html
 * @param integer $attachment_id
 * @param array $attachment
 * @return string
 */
function image_media_send_to_editor($html, $attachment_id, $attachment) {
	$post = get_post($attachment_id);
	if ( substr($post->post_mime_type, 0, 5) == 'image' ) {
		$url = $attachment['url'];
		$align = !empty($attachment['align']) ? $attachment['align'] : 'none';
		$size = !empty($attachment['image-size']) ? $attachment['image-size'] : 'medium';
		$alt = !empty($attachment['image_alt']) ? $attachment['image_alt'] : '';
		$rel = ( strpos( $url, 'attachment_id') || $url === get_attachment_link( $attachment_id ) );

		return get_image_send_to_editor($attachment_id, $attachment['post_excerpt'], $attachment['post_title'], $align, $url, $rel, $size, $alt);
	}

	return $html;
}

/**
 * Retrieves the attachment fields to edit form fields.
 *
 * @since 2.5.0
 *
 * @param WP_Post $post
 * @param array $errors
 * @return array
 */
function get_attachment_fields_to_edit($post, $errors = null) {
	if ( is_int($post) )
		$post = get_post($post);
	if ( is_array($post) )
		$post = new WP_Post( (object) $post );

	$image_url = wp_get_attachment_url($post->ID);

	$edit_post = sanitize_post($post, 'edit');

	$form_fields = array(
		'post_title'   => array(
			'label'      => __('Title'),
			'value'      => $edit_post->post_title
		),
		'image_alt'   => array(),
		'post_excerpt' => array(
			'label'      => __('Caption'),
			'input'      => 'html',
			'html'       => wp_caption_input_textarea($edit_post)
		),
		'post_content' => array(
			'label'      => __('Description'),
			'value'      => $edit_post->post_content,
			'input'      => 'textarea'
		),
		'url'          => array(
			'label'      => __('Link URL'),
			'input'      => 'html',
			'html'       => image_link_input_fields($post, get_option('image_default_link_type')),
			'helps'      => __('Enter a link URL or click above for presets.')
		),
		'menu_order'   => array(
			'label'      => __('Order'),
			'value'      => $edit_post->menu_order
		),
		'image_url'	=> array(
			'label'      => __('File URL'),
			'input'      => 'html',
			'html'       => "<input type='text' class='text urlfield' readonly='readonly' name='attachments[$post->ID][url]' value='" . esc_attr($image_url) . "' /><br />",
			'value'      => wp_get_attachment_url($post->ID),
			'helps'      => __('Location of the uploaded file.')
		)
	);

	foreach ( get_attachment_taxonomies($post) as $taxonomy ) {
		$t = (array) get_taxonomy($taxonomy);
		if ( ! $t['public'] || ! $t['show_ui'] )
			continue;
		if ( empty($t['label']) )
			$t['label'] = $taxonomy;
		if ( empty($t['args']) )
			$t['args'] = array();

		$terms = get_object_term_cache($post->ID, $taxonomy);
		if ( false === $terms )
			$terms = wp_get_object_terms($post->ID, $taxonomy, $t['args']);

		$values = array();

		foreach ( $terms as $term )
			$values[] = $term->slug;
		$t['value'] = join(', ', $values);

		$form_fields[$taxonomy] = $t;
	}

	// Merge default fields with their errors, so any key passed with the error (e.g. 'error', 'helps', 'value') will replace the default
	// The recursive merge is easily traversed with array casting: foreach ( (array) $things as $thing )
	$form_fields = array_merge_recursive($form_fields, (array) $errors);

	// This was formerly in image_attachment_fields_to_edit().
	if ( substr($post->post_mime_type, 0, 5) == 'image' ) {
		$alt = get_post_meta($post->ID, '_wp_attachment_image_alt', true);
		if ( empty($alt) )
			$alt = '';

		$form_fields['post_title']['required'] = true;

		$form_fields['image_alt'] = array(
			'value' => $alt,
			'label' => __('Alternative Text'),
			'helps' => __('Alt text for the image, e.g. &#8220;The Mona Lisa&#8221;')
		);

		$form_fields['align'] = array(
			'label' => __('Alignment'),
			'input' => 'html',
			'html'  => image_align_input_fields($post, get_option('image_default_align')),
		);

		$form_fields['image-size'] = image_size_input_fields( $post, get_option('image_default_size', 'medium') );

	} else {
		unset( $form_fields['image_alt'] );
	}

	/**
	 * Filters the attachment fields to edit.
	 *
	 * @since 2.5.0
	 *
	 * @param array   $form_fields An array of attachment form fields.
	 * @param WP_Post $post        The WP_Post attachment object.
	 */
	$form_fields = apply_filters( 'attachment_fields_to_edit', $form_fields, $post );

	return $form_fields;
}

/**
 * Retrieve HTML for media items of post gallery.
 *
 * The HTML markup retrieved will be created for the progress of SWF Upload
 * component. Will also create link for showing and hiding the form to modify
 * the image attachment.
 *
 * @since 2.5.0
 *
 * @global WP_Query $wp_the_query
 *
 * @param int $post_id Optional. Post ID.
 * @param array $errors Errors for attachment, if any.
 * @return string
 */
function get_media_items( $post_id, $errors ) {
	$attachments = array();
	if ( $post_id ) {
		$post = get_post($post_id);
		if ( $post && $post->post_type == 'attachment' )
			$attachments = array($post->ID => $post);
		else
			$attachments = get_children( array( 'post_parent' => $post_id, 'post_type' => 'attachment', 'orderby' => 'menu_order ASC, ID', 'order' => 'DESC') );
	} else {
		if ( is_array($GLOBALS['wp_the_query']->posts) )
			foreach ( $GLOBALS['wp_the_query']->posts as $attachment )
				$attachments[$attachment->ID] = $attachment;
	}

	$output = '';
	foreach ( (array) $attachments as $id => $attachment ) {
		if ( $attachment->post_status == 'trash' )
			continue;
		if ( $item = get_media_item( $id, array( 'errors' => isset($errors[$id]) ? $errors[$id] : null) ) )
			$output .= "\n<div id='media-item-$id' class='media-item child-of-$attachment->post_parent preloaded'><div class='progress hidden'><div class='bar'></div></div><div id='media-upload-error-$id' class='hidden'></div><div class='filename hidden'></div>$item\n</div>";
	}

	return $output;
}

/**
 * Retrieve HTML form for modifying the image attachment.
 *
 * @since 2.5.0
 *
 * @global string $redir_tab
 *
 * @param int $attachment_id Attachment ID for modification.
 * @param string|array $args Optional. Override defaults.
 * @return string HTML form for attachment.
 */
function get_media_item( $attachment_id, $args = null ) {
	global $redir_tab;

	if ( ( $attachment_id = intval( $attachment_id ) ) && $thumb_url = wp_get_attachment_image_src( $attachment_id, 'thumbnail', true ) )
		$thumb_url = $thumb_url[0];
	else
		$thumb_url = false;

	$post = get_post( $attachment_id );
	$current_post_id = !empty( $_GET['post_id'] ) ? (int) $_GET['post_id'] : 0;

	$default_args = array(
		'errors' => null,
		'send' => $current_post_id ? post_type_supports( get_post_type( $current_post_id ), 'editor' ) : true,
		'delete' => true,
		'toggle' => true,
		'show_title' => true
	);
	$args = wp_parse_args( $args, $default_args );

	/**
	 * Filters the arguments used to retrieve an image for the edit image form.
	 *
	 * @since 3.1.0
	 *
	 * @see get_media_item
	 *
	 * @param array $args An array of arguments.
	 */
	$r = apply_filters( 'get_media_item_args', $args );

	$toggle_on  = __( 'Show' );
	$toggle_off = __( 'Hide' );

	$file = get_attached_file( $post->ID );
	$filename = esc_html( wp_basename( $file ) );
	$title = esc_attr( $post->post_title );

	$post_mime_types = get_post_mime_types();
	$keys = array_keys( wp_match_mime_types( array_keys( $post_mime_types ), $post->post_mime_type ) );
	$type = reset( $keys );
	$type_html = "<input type='hidden' id='type-of-$attachment_id' value='" . esc_attr( $type ) . "' />";

	$form_fields = get_attachment_fields_to_edit( $post, $r['errors'] );

	if ( $r['toggle'] ) {
		$class = empty( $r['errors'] ) ? 'startclosed' : 'startopen';
		$toggle_links = "
	<a class='toggle describe-toggle-on' href='#'>$toggle_on</a>
	<a class='toggle describe-toggle-off' href='#'>$toggle_off</a>";
	} else {
		$class = '';
		$toggle_links = '';
	}

	$display_title = ( !empty( $title ) ) ? $title : $filename; // $title shouldn't ever be empty, but just in case
	$display_title = $r['show_title'] ? "<div class='filename new'><span class='title'>" . wp_html_excerpt( $display_title, 60, '&hellip;' ) . "</span></div>" : '';

	$gallery = ( ( isset( $_REQUEST['tab'] ) && 'gallery' == $_REQUEST['tab'] ) || ( isset( $redir_tab ) && 'gallery' == $redir_tab ) );
	$order = '';

	foreach ( $form_fields as $key => $val ) {
		if ( 'menu_order' == $key ) {
			if ( $gallery )
				$order = "<div class='menu_order'> <input class='menu_order_input' type='text' id='attachments[$attachment_id][menu_order]' name='attachments[$attachment_id][menu_order]' value='" . esc_attr( $val['value'] ). "' /></div>";
			else
				$order = "<input type='hidden' name='attachments[$attachment_id][menu_order]' value='" . esc_attr( $val['value'] ) . "' />";

			unset( $form_fields['menu_order'] );
			break;
		}
	}

	$media_dims = '';
	$meta = wp_get_attachment_metadata( $post->ID );
	if ( isset( $meta['width'], $meta['height'] ) )
		$media_dims .= "<span id='media-dims-$post->ID'>{$meta['width']}&nbsp;&times;&nbsp;{$meta['height']}</span> ";

	/**
	 * Filters the media metadata.
	 *
	 * @since 2.5.0
	 *
	 * @param string  $media_dims The HTML markup containing the media dimensions.
	 * @param WP_Post $post       The WP_Post attachment object.
	 */
	$media_dims = apply_filters( 'media_meta', $media_dims, $post );

	$image_edit_button = '';
	if ( wp_attachment_is_image( $post->ID ) && wp_image_editor_supports( array( 'mime_type' => $post->post_mime_type ) ) ) {
		$nonce = wp_create_nonce( "image_editor-$post->ID" );
		$image_edit_button = "<input type='button' id='imgedit-open-btn-$post->ID' onclick='imageEdit.open( $post->ID, \"$nonce\" )' class='button' value='" . esc_attr__( 'Edit Image' ) . "' /> <span class='spinner'></span>";
	}

	$attachment_url = get_permalink( $attachment_id );

	$item = "
	$type_html
	$toggle_links
	$order
	$display_title
	<table class='slidetoggle describe $class'>
		<thead class='media-item-info' id='media-head-$post->ID'>
		<tr>
			<td class='A1B1' id='thumbnail-head-$post->ID'>
			<p><a href='$attachment_url' target='_blank'><img class='thumbnail' src='$thumb_url' alt='' /></a></p>
			<p>$image_edit_button</p>
			</td>
			<td>
			<p><strong>" . __('File name:') . "</strong> $filename</p>
			<p><strong>" . __('File type:') . "</strong> $post->post_mime_type</p>
			<p><strong>" . __('Upload date:') . "</strong> " . mysql2date( __( 'F j, Y' ), $post->post_date ). '</p>';
			if ( !empty( $media_dims ) )
				$item .= "<p><strong>" . __('Dimensions:') . "</strong> $media_dims</p>\n";

			$item .= "</td></tr>\n";

	$item .= "
		</thead>
		<tbody>
		<tr><td colspan='2' class='imgedit-response' id='imgedit-response-$post->ID'></td></tr>\n
		<tr><td style='display:none' colspan='2' class='image-editor' id='image-editor-$post->ID'></td></tr>\n
		<tr><td colspan='2'><p class='media-types media-types-required-info'>" . sprintf( __( 'Required fields are marked %s' ), '<span class="required">*</span>' ) . "</p></td></tr>\n";

	$defaults = array(
		'input'      => 'text',
		'required'   => false,
		'value'      => '',
		'extra_rows' => array(),
	);

	if ( $r['send'] ) {
		$r['send'] = get_submit_button( __( 'Insert into Post' ), '', "send[$attachment_id]", false );
	}

	$delete = empty( $r['delete'] ) ? '' : $r['delete'];
	if ( $delete && current_user_can( 'delete_post', $attachment_id ) ) {
		if ( !EMPTY_TRASH_DAYS ) {
			$delete = "<a href='" . wp_nonce_url( "post.php?action=delete&amp;post=$attachment_id", 'delete-post_' . $attachment_id ) . "' id='del[$attachment_id]' class='delete-permanently'>" . __( 'Delete Permanently' ) . '</a>';
		} elseif ( !MEDIA_TRASH ) {
			$delete = "<a href='#' class='del-link' onclick=\"document.getElementById('del_attachment_$attachment_id').style.display='block';return false;\">" . __( 'Delete' ) . "</a>
			 <div id='del_attachment_$attachment_id' class='del-attachment' style='display:none;'>" .
			 /* translators: %s: file name */
			'<p>' . sprintf( __( 'You are about to delete %s.' ), '<strong>' . $filename . '</strong>' ) . "</p>
			 <a href='" . wp_nonce_url( "post.php?action=delete&amp;post=$attachment_id", 'delete-post_' . $attachment_id ) . "' id='del[$attachment_id]' class='button'>" . __( 'Continue' ) . "</a>
			 <a href='#' class='button' onclick=\"this.parentNode.style.display='none';return false;\">" . __( 'Cancel' ) . "</a>
			 </div>";
		} else {
			$delete = "<a href='" . wp_nonce_url( "post.php?action=trash&amp;post=$attachment_id", 'trash-post_' . $attachment_id ) . "' id='del[$attachment_id]' class='delete'>" . __( 'Move to Trash' ) . "</a>
			<a href='" . wp_nonce_url( "post.php?action=untrash&amp;post=$attachment_id", 'untrash-post_' . $attachment_id ) . "' id='undo[$attachment_id]' class='undo hidden'>" . __( 'Undo' ) . "</a>";
		}
	} else {
		$delete = '';
	}

	$thumbnail = '';
	$calling_post_id = 0;
	if ( isset( $_GET['post_id'] ) ) {
		$calling_post_id = absint( $_GET['post_id'] );
	} elseif ( isset( $_POST ) && count( $_POST ) ) {// Like for async-upload where $_GET['post_id'] isn't set
		$calling_post_id = $post->post_parent;
	}
	if ( 'image' == $type && $calling_post_id && current_theme_supports( 'post-thumbnails', get_post_type( $calling_post_id ) )
		&& post_type_supports( get_post_type( $calling_post_id ), 'thumbnail' ) && get_post_thumbnail_id( $calling_post_id ) != $attachmen