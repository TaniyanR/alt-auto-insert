<?php
/**
 * Plugin Name: alt自動挿入
 * Plugin URI: https://github.com/TaniyanR/alt-auto-insert
 * Description: WordPressの記事に挿入した画像のalt属性へ、記事タイトルを自動で設定する軽量プラグインです。
 * Version: 1.1.0
 * Author: TaniyanR
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Text Domain: alt-auto-insert
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Alt_Auto_Insert {
	const OPTION_NAME = 'alt_auto_insert_settings';
	const VERSION     = '1.1.0';

	/**
	 * Boot plugin hooks.
	 */
	public static function init() {
		add_filter( 'wp_insert_post_data', array( __CLASS__, 'filter_post_data' ), 20, 2 );
		add_filter( 'image_send_to_editor', array( __CLASS__, 'filter_classic_editor_image' ), 20, 8 );
		add_filter( 'post_thumbnail_html', array( __CLASS__, 'filter_post_thumbnail_html' ), 20, 5 );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_assets' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'add_settings_link' ) );
	}

	/**
	 * Add defaults on activation without overwriting existing settings.
	 */
	public static function activate() {
		if ( false === get_option( self::OPTION_NAME, false ) ) {
			add_option( self::OPTION_NAME, self::get_defaults() );
		}
	}

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	private static function get_defaults() {
		return array(
			'post_types'      => array( 'post', 'page' ),
			'content_images'  => 1,
			'featured_images' => 1,
		);
	}

	/**
	 * Get normalized settings.
	 *
	 * @return array
	 */
	private static function get_settings() {
		$saved = get_option( self::OPTION_NAME, array() );
		$saved = is_array( $saved ) ? $saved : array();

		return wp_parse_args( $saved, self::get_defaults() );
	}

	/**
	 * Determine whether a post type is enabled.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	private static function is_post_type_enabled( $post_type ) {
		$settings   = self::get_settings();
		$post_types = isset( $settings['post_types'] ) && is_array( $settings['post_types'] ) ? $settings['post_types'] : array();

		return in_array( $post_type, $post_types, true );
	}

	/**
	 * Get the current editor post title.
	 *
	 * @return string
	 */
	private static function get_current_editor_title() {
		$post_id = isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : 0;
		if ( ! $post_id && isset( $_REQUEST['post'] ) ) {
			$post_id = absint( $_REQUEST['post'] );
		}
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$title = $post_id ? get_the_title( $post_id ) : '';
		return trim( wp_strip_all_tags( (string) $title ) );
	}

	/**
	 * Load the lightweight Gutenberg helper only for enabled post types.
	 */
	public static function enqueue_block_editor_assets() {
		$settings = self::get_settings();
		if ( empty( $settings['content_images'] ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || empty( $screen->post_type ) || ! self::is_post_type_enabled( (string) $screen->post_type ) ) {
			return;
		}

		wp_enqueue_script(
			'alt-auto-insert-editor',
			plugins_url( 'assets/editor-alt.js', __FILE__ ),
			array( 'wp-compose', 'wp-data', 'wp-element', 'wp-hooks' ),
			self::VERSION,
			true
		);
	}

	/**
	 * Fill an empty alt immediately when Classic Editor inserts an image.
	 * Existing non-empty alt values are never overwritten.
	 *
	 * @param string $html    Image HTML.
	 * @param int    $id      Attachment ID.
	 * @param string $caption Caption.
	 * @param string $title   Attachment title.
	 * @param string $align   Alignment.
	 * @param string $url     Image URL.
	 * @param string $size    Image size.
	 * @param string $alt     Existing alt text.
	 * @return string
	 */
	public static function filter_classic_editor_image( $html, $id, $caption, $title, $align, $url, $size, $alt ) {
		unset( $id, $caption, $title, $align, $url, $size );

		$settings = self::get_settings();
		if ( empty( $settings['content_images'] ) || '' !== trim( (string) $alt ) ) {
			return $html;
		}

		$post_type = '';
		if ( isset( $_REQUEST['post_id'] ) ) {
			$post_type = (string) get_post_type( absint( $_REQUEST['post_id'] ) );
		} elseif ( isset( $_REQUEST['post'] ) ) {
			$post_type = (string) get_post_type( absint( $_REQUEST['post'] ) );
		}
		if ( $post_type && ! self::is_post_type_enabled( $post_type ) ) {
			return $html;
		}

		$post_title = self::get_current_editor_title();
		if ( '' === $post_title ) {
			return $html;
		}

		return self::fill_empty_alt_attributes( $html, $post_title );
	}

	/**
	 * Fill missing or empty alt attributes in post content before it is stored.
	 * Existing non-empty alt values are never overwritten.
	 *
	 * @param array $data    Slashed post data.
	 * @param array $postarr Raw post data.
	 * @return array
	 */
	public static function filter_post_data( $data, $postarr ) {
		unset( $postarr );
		$settings = self::get_settings();

		if ( empty( $settings['content_images'] ) ) {
			return $data;
		}

		$post_type = isset( $data['post_type'] ) ? (string) $data['post_type'] : '';
		if ( ! $post_type || 'revision' === $post_type || ! self::is_post_type_enabled( $post_type ) ) {
			return $data;
		}

		$title = isset( $data['post_title'] ) ? wp_strip_all_tags( wp_unslash( $data['post_title'] ) ) : '';
		$title = trim( $title );
		if ( '' === $title ) {
			return $data;
		}

		$content = isset( $data['post_content'] ) ? wp_unslash( $data['post_content'] ) : '';
		if ( '' === $content || false === stripos( $content, '<img' ) ) {
			return $data;
		}

		$updated = self::fill_empty_alt_attributes( $content, $title );
		if ( $updated !== $content ) {
			$data['post_content'] = wp_slash( $updated );
		}

		return $data;
	}

	/**
	 * Fill a missing or empty alt on featured-image HTML at render time.
	 * This avoids changing attachment metadata globally when one image is reused.
	 *
	 * @param string       $html              Thumbnail HTML.
	 * @param int          $post_id           Post ID.
	 * @param int          $post_thumbnail_id Thumbnail attachment ID.
	 * @param string|int[] $size              Requested image size.
	 * @param string|array $attr              Attributes.
	 * @return string
	 */
	public static function filter_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		unset( $post_thumbnail_id, $size, $attr );

		$settings = self::get_settings();
		if ( empty( $settings['featured_images'] ) || empty( $html ) ) {
			return $html;
		}

		$post_type = get_post_type( $post_id );
		if ( ! $post_type || ! self::is_post_type_enabled( $post_type ) ) {
			return $html;
		}

		$title = trim( wp_strip_all_tags( get_the_title( $post_id ) ) );
		if ( '' === $title ) {
			return $html;
		}

		return self::fill_empty_alt_attributes( $html, $title );
	}

	/**
	 * Fill missing or whitespace-only alt attributes on image tags.
	 *
	 * @param string $html HTML content.
	 * @param string $alt  Alt text to set.
	 * @return string
	 */
	private static function fill_empty_alt_attributes( $html, $alt ) {
		if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
			return $html;
		}

		$processor = new WP_HTML_Tag_Processor( $html );

		while ( $processor->next_tag( 'img' ) ) {
			$current_alt = $processor->get_attribute( 'alt' );

			if ( is_string( $current_alt ) && '' !== trim( $current_alt ) ) {
				continue;
			}

			$processor->set_attribute( 'alt', $alt );
		}

		return $processor->get_updated_html();
	}

	/**
	 * Add settings page under Settings.
	 */
	public static function add_settings_page() {
		add_options_page(
			'alt自動挿入',
			'alt自動挿入',
			'manage_options',
			'alt-auto-insert',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public static function register_settings() {
		register_setting(
			'alt_auto_insert_group',
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
				'default'           => self::get_defaults(),
			)
		);

		add_settings_section(
			'alt_auto_insert_main',
			'基本設定',
			array( __CLASS__, 'render_settings_section' ),
			'alt-auto-insert'
		);

		add_settings_field(
			'post_types',
			'対象の投稿タイプ',
			array( __CLASS__, 'render_post_types_field' ),
			'alt-auto-insert',
			'alt_auto_insert_main'
		);

		add_settings_field(
			'content_images',
			'本文画像',
			array( __CLASS__, 'render_content_images_field' ),
			'alt-auto-insert',
			'alt_auto_insert_main'
		);

		add_settings_field(
			'featured_images',
			'アイキャッチ画像',
			array( __CLASS__, 'render_featured_images_field' ),
			'alt-auto-insert',
			'alt_auto_insert_main'
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize_settings( $input ) {
		$input      = is_array( $input ) ? $input : array();
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		$selected   = isset( $input['post_types'] ) && is_array( $input['post_types'] ) ? array_map( 'sanitize_key', $input['post_types'] ) : array();
		$selected   = array_values( array_intersect( $selected, $post_types ) );
		$selected   = array_values( array_diff( $selected, array( 'attachment' ) ) );

		return array(
			'post_types'      => $selected,
			'content_images'  => empty( $input['content_images'] ) ? 0 : 1,
			'featured_images' => empty( $input['featured_images'] ) ? 0 : 1,
		);
	}

	/**
	 * Settings section description.
	 */
	public static function render_settings_section() {
		echo '<p>alt属性が未設定または空の画像だけに記事タイトルを設定します。既存のaltは上書きしません。</p>';
	}

	/**
	 * Render post type checkboxes.
	 */
	public static function render_post_types_field() {
		$settings   = self::get_settings();
		$selected   = isset( $settings['post_types'] ) && is_array( $settings['post_types'] ) ? $settings['post_types'] : array();
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		foreach ( $post_types as $post_type ) {
			if ( 'attachment' === $post_type->name ) {
				continue;
			}

			printf(
				'<label style="display:block;margin-bottom:6px;"><input type="checkbox" name="%1$s[post_types][]" value="%2$s" %3$s> %4$s <code>%2$s</code></label>',
				esc_attr( self::OPTION_NAME ),
				esc_attr( $post_type->name ),
				checked( in_array( $post_type->name, $selected, true ), true, false ),
				esc_html( $post_type->labels->singular_name )
			);
		}
	}

	/**
	 * Render content image setting.
	 */
	public static function render_content_images_field() {
		$settings = self::get_settings();
		printf(
			'<label><input type="checkbox" name="%1$s[content_images]" value="1" %2$s> エディターで画像を挿入した時、空のaltへ記事タイトルを設定する</label><p class="description">Gutenbergとクラシックエディターに対応し、保存時にも空altを補完します。</p>',
			esc_attr( self::OPTION_NAME ),
			checked( ! empty( $settings['content_images'] ), true, false )
		);
	}

	/**
	 * Render featured image setting.
	 */
	public static function render_featured_images_field() {
		$settings = self::get_settings();
		printf(
			'<label><input type="checkbox" name="%1$s[featured_images]" value="1" %2$s> アイキャッチ画像の空altを表示時に記事タイトルで補完する</label><p class="description">添付ファイルのaltメタデータ自体は変更しないため、同じ画像を別の記事で再利用しても影響しません。</p>',
			esc_attr( self::OPTION_NAME ),
			checked( ! empty( $settings['featured_images'] ), true, false )
		);
	}

	/**
	 * Render settings page.
	 */
	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1>alt自動挿入</h1>
			<p>画像のaltが空の場合だけ、記事タイトルを自動設定します。</p>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'alt_auto_insert_group' );
				do_settings_sections( 'alt-auto-insert' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Add Settings link to the Plugins screen.
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	public static function add_settings_link( $links ) {
		$url = admin_url( 'options-general.php?page=alt-auto-insert' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">設定</a>' );
		return $links;
	}
}

register_activation_hook( __FILE__, array( 'Alt_Auto_Insert', 'activate' ) );
Alt_Auto_Insert::init();
