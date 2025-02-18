<?php
/*
 * Plugin Name: JSM Test Revision Array
 * Text Domain: jsm-test-revision-array
 * Domain Path: /languages
 * Plugin URI:
 * Assets URI:
 * Author: JS Morisset
 * Author URI: https://surniaulula.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Description: A plugin to test WP revisions with arrays.
 * Requires PHP: 7.4.33
 * Requires At Least: 5.9
 * Tested Up To: 6.7.2
 * Version: 1.0.1
 *
 * Version Numbering: {major}.{minor}.{bugfix}[-{stage}.{level}]
 *
 *      {major}         Major structural code changes and/or incompatible API changes (ie. breaking changes).
 *      {minor}         New functionality was added or improved in a backwards-compatible manner.
 *      {bugfix}        Backwards-compatible bug fixes or small improvements.
 *      {stage}.{level} Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).
 *
 * Copyright 2017-2025 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'JsmTestRevisionArray' ) ) {

	class JsmTestRevisionArray {

		private static $instance = null;	// JsmDecolorize class object.

		private $meta_key = '_random_array';

		public function __construct() {

			add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
		}

		public static function &get_instance() {

			if ( null === self::$instance ) {

				self::$instance = new self;
			}

			return self::$instance;
		}

		public function wp_loaded() {

			$this->register_meta( $object_type = 'post', $this->meta_key );

			add_action( 'current_screen', array( $this, 'current_screen' ), 100, 1 );
		}

		public function current_screen( $screen = false ) {

			if ( empty( $screen->id ) || 'page' !== $screen->id ) {

				return;

			} elseif ( empty( $_GET[ 'post' ] ) || ! is_numeric( $_GET[ 'post' ] ) ) {

				return;
			}

			/*
			 * Create an array with random content and save it to the post metadata.
			 */
			$post_id = (int) $_GET[ 'post' ];

			$meta_value = array( md5( rand() ) );

			update_metadata( 'post', $post_id, $this->meta_key, $meta_value );
		}

		protected function register_meta( $object_type, $meta_key ) {

			$meta_title = _x( 'JSM Test Revision Array', 'meta title', 'jsm-test-revision-array' );

			register_meta( $object_type, $meta_key, $args = array(
				'type'              => 'array',
				'description'       => $meta_title,
				'default'           => array(),
				'single'            => true,
				'sanitize_callback' => null,
				'auth_callback'     => null,
				'show_in_rest'      => false,
				'revisions_enabled' => 'post' === $object_type? true : false,
			) );

			add_filter( '_wp_post_revision_fields', array( $this, 'revision_fields_meta_title' ), 10, 2 );
		}

		public function revision_fields_meta_title( $fields, $post ) {

			$meta_title = _x( 'JSM Test Revision Array', 'meta title', 'jsm-test-revision-array' );

			$fields[ $this->meta_key ] = sprintf( _x( '%s Metadata', 'meta title', 'jsm-test-revision-array' ), $meta_title );

			add_filter( '_wp_post_revision_field_' . $this->meta_key, array( $this, 'get_revision_fields_md_opts' ), 10, 3 );

			return $fields;
		}

		public function get_revision_fields_md_opts( $md_opts, $meta_key, $wp_obj ) {

			if ( is_string( $md_opts ) ) {	// Nothing to do.

				return $md_opts;

			} elseif ( is_array( $md_opts ) ) {	// Convert array to string.

				return var_export( $md_opts, true );
			}

			return '';
		}
	}

	JsmTestRevisionArray::get_instance();
}
