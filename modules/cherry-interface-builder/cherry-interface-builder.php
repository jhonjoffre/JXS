<?php
/**
 * Module Name: Interface Builder
 * Description: The module for the creation of interfaces in the WordPress admin panel
 * Version: 1.0.0
 * Author: Cherry Team
 * Author URI: http://www.cherryframework.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package    Cherry_Framework
 * @subpackage Modules
 * @version    1.0.0
 * @author     Cherry Team <cherryframework@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016, Cherry Team
 * @link       http://www.cherryframework.com/
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Cherry_Interface_Builder' ) ) {

	/**
	 * Class Cherry Interface Builder.
	 *
	 * @since 1.0.0
	 */
	class Cherry_Interface_Builder {

		/**
		 * Module settings.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    array
		 */
		private $args = array(
			'views'        => array(
				'section'                   => 'inc/views/section.php',
				'component-tab-vertical'    => 'inc/views/component-tab-vertical.php',
				'component-tab-horizontal'  => 'inc/views/component-tab-horizontal.php',
				'component-toggle'          => 'inc/views/component-toggle.php',
				'component-accordion'       => 'inc/views/component-accordion.php',
				'component-repeater'        => 'inc/views/component-repeater.php',
				'settings'                  => 'inc/views/settings.php',
				'control'                   => 'inc/views/control.php',
				'settings-children-title'   => 'inc/views/settings-children-title.php',
				'tab-children-title'        => 'inc/views/tab-children-title.php',
				'toggle-children-title'     => 'inc/views/toggle-children-title.php',
				'html'                      => 'inc/views/html.php',
			),
			'views_args' => array(
				'parent'      => '',
				'type'        => '',
				'view'        => '',
				'html'        => '',
				'scroll'      => false,
				'master'      => false,
				'title'       => '',
				'description' => '',
			),
		);

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $instance = null;

		/**
		 * UI element instance.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    object
		 */
		public $ui_elements = null;

		/**
		 * The structure of the interface elements.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    array
		 */
		private $structure = array();

		/**
		 * Cherry_Interface_Builder constructor.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct( $core, array $args = array() ) {
			$this->args = array_merge_recursive(
				$args,
				$this->args
			);

			$this->ui_elements = $core->init_module( 'cherry-ui-elements' );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		/**
		 * Register element type section.
		 *
		 * @since  1.0.0
		 * @access public
		 * @param  array $args Options section.
		 * @return void
		 */
		public function register_section( array $args = array() ) {
			$this->add_new_element( $args, 'section' );
		}

		/**
		 * Register element type component.
		 *
		 * @since  1.0.0
		 * @access public
		 * @param  array $args Options component.
		 * @return void
		 */
		public function register_component( array $args = array() ) {
			$this->add_new_element( $args, 'component' );
		}

		/**
		 * Register element type settings.
		 *
		 * @since  1.0.0
		 * @access public
		 * @param  array $args Options settings.
		 * @return void
		 */
		public function register_settings( array $args = array() ) {
			$this->add_new_element( $args, 'settings' );
		}

		/**
		 * Register element type control.
		 *
		 * @since  1.0.0
		 * @access public
		 * @param  array $args Options control.
		 * @return void
		 */
		public function register_control( array $args = array() ) {
			$this->add_new_element( $args, 'control' );
		}

		/**
		 * Register element type control.
		 *
		 * @since  1.0.0
		 * @access public
		 * @param  array $args Options control.
		 * @return void
		 */
		public function register_html( array $args = array() ) {
			$this->add_new_element( $args, 'html' );
		}

		/**
		 * This function adds a new element to the structure.
		 *
		 * @since  1.0.0
		 * @access protected
		 * @param  array  $args Options new element.
		 * @param  string $type Type new element.
		 * @return void
		 */
		protected function add_new_element( array $args = array(), $type = 'section' ) {
			if ( ! isset( $args[0] ) && ! is_array( current( $args ) ) ) {
					$this->structure[ $args['id'] ] = $args;
			} else {
				foreach ( $args as $key => $value ) {

					if ( 'control' !== $type ) {
						$value['type'] = $type;
					}

					$this->structure[ $key ] = $value;
				}
			}
		}

		/**
		 * Sorts the elements of the structure, adding child items to the parent.
		 *
		 * @since  1.0.0
		 * @access protected
		 * @param  array  $structure  The original structure of the elements.
		 * @param  string $parent_key The key of the parent element.
		 * @return array
		 */
		protected function sort_structure( array $structure = array(), $parent_key = null ) {
			$new_array = array();

			foreach ( $structure as $key => $value ) {
				if (
					( null === $parent_key && ! isset( $value['parent'] ) )
					|| null === $parent_key && ! isset( $structure[ $value['parent'] ] )
					|| ( isset( $value['parent'] ) && $value['parent'] === $parent_key )
				) {

					if ( ! isset( $value['id'] ) ) {
						$value['id'] = $parent_key ? $parent_key . '-' . $key : $key ;
					}
					if ( ! isset( $value['name'] ) ) {
						$value['name'] = $parent_key ? $parent_key . '-' . $key : $key ;
					}
					$new_array[ $key ] = $value;

					$children = $this->sort_structure( $structure, $key );
					if ( ! empty( $children ) ) {
						$new_array[ $key ]['children'] = $children;
					}
				}
			}

			return $new_array;
		}

		/**
		 * Get view for interface elements.
		 *
		 * @since  1.0.0
		 * @access protected
		 * @param  string $type View type.
		 * @param  array  $args Input data.
		 * @return string
		 */
		protected function get_view( $type = 'control', array $args = array() ) {

			if ( is_array( $args ) ) {
				extract( $args, EXTR_SKIP );
			}

			if ( empty( $view ) ) {
				$path = dirname( __FILE__ ) . '/';
				$path .= ( array_key_exists( $type, $this->args['views'] ) ) ? $this->args['views'][ $type ] : $this->args['views']['control'] ;
			} else {
				$path = $view;
			}

			ob_start();

			if ( file_exists( $path ) ) {
				require $path;
			}

			return ltrim( ob_get_clean() );
		}

		/**
		 * Render interface elements.
		 *
		 * @since  1.0.0
		 * @access public
		 * @param  array $args The original structure of the elements.
		 * @param  bool  $echo Input data.
		 * @return string
		 */
		public function render( array $args = array(), $echo = true ) {

			if ( empty( $args ) ) {
				$args = $this->structure;
			}

			if ( empty( $args ) ) {
				return false;
			}

			$sorted_structure = $this->sort_structure( $args );

			$output = $this->build( $sorted_structure );
			$output = str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $output );

			return $this->output_method( $output, $echo );
		}

		/**
		 * Render interface elements.
		 *
		 * @since  1.0.0
		 * @access protected
		 * @param  array $args Input data.
		 * @return string
		 */
		protected function build( array $args = array() ) {
			$output = '';
			$views = $this->args['views'];

			foreach ( $args as $key => $value ) {
				$value = wp_parse_args(
					$value,
					$this->args['views_args']
				);

				$value['class'] = '' ;
				if ( $value['scroll'] ) {
					$value['class'] .= 'cherry-scroll';
				}
				if ( $value['master'] ) {
					$value['class'] .= $value['master'];
				}

				$type = array_key_exists( $value['type'], $views ) ? $value['type'] : 'field' ;
				$has_child = isset( $value['children'] ) && is_array( $value['children'] ) && ! empty( $value['children'] );

				switch ( $type ) {
					case 'component-tab-vertical':
					case 'component-tab-horizontal':
						if ( $has_child ) {
							$value['tabs'] = '';

							foreach ( $value['children'] as $key_children => $value_children ) {
								$value['tabs'] .= $this->get_view( 'tab-children-title', $value_children );

								unset( $value['children'][ $key_children ]['title'] );
							}
						}
					break;

					case 'component-toggle':
					case 'component-accordion':
						if ( $has_child ) {
							foreach ( $value['children'] as $key_children => $value_children ) {
								$value['children'][ $key_children ]['title_in_view'] = $this->get_view( 'toggle-children-title', $value_children );
							}
						}
					break;

					case 'settings':
						if ( isset( $value['title'] ) && $value['title'] ) {
							$value['title'] = isset( $value['title_in_view'] ) ? $value['title_in_view'] : $this->get_view( 'settings-children-title', $value );
						}
					break;

					case 'html':
						$value['children'] = $value['html'];
					break;

					case 'field':
						if ( isset( $value['master'] ) ) {
							$value['master'] = '';
						}
						$value['children'] = $this->ui_elements->get_ui_element_instance( $value['type'], $value )->render();
					break;
				}

				if ( $has_child ) {
					$value['children'] = $this->build( $value['children'] );
				}

				$output .= $this->get_view( $type, $value );
			}

			return $output;
		}

		/**
		 * Output HTML.
		 *
		 * @since  1.0.0
		 * @access protected
		 * @param  string  $output Output HTML.
		 * @param  boolean $echo   Output type.
		 * @return string
		 */
		protected function output_method( $output = '', $echo = true ) {
			if ( ! filter_var( $echo, FILTER_VALIDATE_BOOLEAN ) ) {
				return $output;
			} else {
				echo $output;
			}
		}

		/**
		 * Enqueue javascript and stylesheet interface builder.
		 *
		 * @since  4.0.0
		 * @access public
		 * @return void
		 */
		public function enqueue_assets() {
			wp_enqueue_script(
				'cherry-interface-builder',
				esc_url( Cherry_Core::base_url( 'inc/assets/min/cherry-interface-builder.min.js', __FILE__ ) ),
				array( 'jquery' ),
				'1.0.0',
				true
			);
			wp_enqueue_style(
				'cherry-interface-builder',
				esc_url( Cherry_Core::base_url( 'inc/assets/min/cherry-interface-builder.min.css', __FILE__ ) ),
				array(),
				'1.0.0',
				'all'
			);
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return object
		 */
		public static function get_instance( $core, $args ) {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self( $core, $args );
			}

			return self::$instance;
		}
	}
}