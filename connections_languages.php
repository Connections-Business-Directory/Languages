<?php
/**
 * An extension for the Connections plugin which adds a metabox for languages.
 *
 * @package   Connections Business Directory Extension - Languages
 * @category  Extension
 * @author    Steven A. Zahm
 * @license   GPL-2.0+
 * @link      http://connections-pro.com
 * @copyright 2021 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Business Directory Extension - Languages
 * Plugin URI:        https://connections-pro.com/add-on/languages/
 * Description:       An extension for the Connections plugin which adds a metabox for languages.
 * Version:           2.0.1
 * Author:            Steven A. Zahm
 * Author URI:        https://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       connections_languages
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists('Connections_Languages') ) {

	class Connections_Languages {

		const VERSION = '2.0.1';

		/**
		 * @var Connections_Languages Stores the instance of this class.
		 *
		 * @access private
		 * @since 1.1
		 */
		private static $instance;

		/**
		 * @var string The absolute path this this file.
		 *
		 * @access private
		 * @since 1.1
		 */
		private static $file = '';

		/**
		 * @var string The URL to the plugin's folder.
		 *
		 * @access private
		 * @since 1.1
		 */
		private static $url = '';

		/**
		 * @var string The absolute path to this plugin's folder.
		 *
		 * @access private
		 * @since 1.1
		 */
		private static $path = '';

		/**
		 * @var string The basename of the plugin.
		 *
		 * @access private
		 * @since 1.0
		 */
		private static $basename = '';

		public function __construct() { /* Do nothing here */ }

		/**
		 * @access public
		 * @since  1.1
		 *
		 * @return Connections_Languages
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Connections_Languages ) ) {

				self::$instance = $self = new self;

				self::$file     = __FILE__;
				self::$url      = plugin_dir_url( self::$file );
				self::$path     = plugin_dir_path( self::$file );
				self::$basename = plugin_basename( self::$file );

				self::loadDependencies();
				self::hooks();

				/**
				 * This should run on the `plugins_loaded` action hook. Since the extension loads on the
				 * `plugins_loaded` action hook, load immediately.
				 */
				cnText_Domain::register(
					'connections_languages',
					self::$basename,
					'load'
				);
			}

			return self::$instance;
		}

		/**
		 * Gets the basename of a plugin.
		 *
		 * @access public
		 * @since  1.1
		 *
		 * @return string
		 */
		public function pluginBasename() {

			return self::$basename;
		}

		/**
		 * Get the absolute directory path (with trailing slash) for the plugin.
		 *
		 * @access public
		 * @since  1.1
		 *
		 * @return string
		 */
		public function pluginPath() {

			return self::$path;
		}

		/**
		 * Get the URL directory path (with trailing slash) for the plugin.
		 *
		 * @access public
		 * @since  1.1
		 *
		 * @return string
		 */
		public function pluginURL() {

			return self::$url;
		}

		/**
		 * Register all the hooks that makes this thing run.
		 *
		 * @access private
		 * @since  1.1
		 */
		private static function hooks() {

			// register_activation_hook( Connections_Languages()->pluginPath() . 'connections_languages.php', array( __CLASS__, 'activate' ) );
			// register_deactivation_hook( Connections_Languages()->pluginPath() . 'connections_languages.php', array( __CLASS__, 'deactivate' ) );

			// Register the metabox and fields.
			add_action( 'cn_metabox', array( __CLASS__, 'registerMetabox') );

			// Register the custom fields CSV Export attributes and processing callback.
			add_filter( 'cn_csv_export_fields_config', array( __CLASS__, 'registerCustomFieldCSVExportConfig' ) );
			add_filter( 'cn_export_header-languages', array( __CLASS__, 'registerCSVExportFieldHeader' ), 10, 3 );
			add_filter( 'cn_export_field-languages', array( __CLASS__, 'registerCustomFieldExportAction' ), 10, 4 );

			// Register the custom fields CSV Import mapping options and processing callback.
			add_filter( 'cncsv_map_import_fields', array( __CLASS__, 'registerCSVImportFieldHeader' ) );
			add_action( 'cncsv_import_fields', array( __CLASS__, 'registerCustomFieldImportAction' ), 10, 3 );

			// Add the business hours option to the admin settings page.
			// This is also required so it'll be rendered by $entry->getContentBlock( 'languages' ).
			add_filter( 'cn_content_blocks', array( __CLASS__, 'settingsOption') );

			// Add the action that'll be run when calling $entry->getContentBlock( 'languages' ) from within a template.
			add_action( 'cn_output_meta_field-languages', array( __CLASS__, 'block' ), 10, 4 );

			// Register the widget.
			add_action( 'widgets_init', array( 'CN_Languages_Widget', 'register' ) );
		}

		/**
		 * The widget.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 * @return void
		 */
		private static function loadDependencies() {

			require_once( Connections_Languages()->pluginPath() . 'includes/class.widgets.php' );
		}

		public static function activate() {}

		public static function deactivate() {}

		/**
		 * Defines the language options.
		 *
		 * Default list is the most spoken languages in the world.
		 * @url http://www.nationsonline.org/oneworld/most_spoken_languages.htm
		 * @url http://www.nationsonline.org/oneworld/languages.htm
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 * @uses   apply_filters()
		 * @return array An indexed array containing the languages.
		 */
		private static function languages() {

			$options = array(
				'ara' => __( 'Arabic', 'connections_languages'),
				'ben' => __( 'Bengali', 'connections_languages'),
				'chi' => __( 'Chinese', 'connections_languages'),
				'eng' => __( 'English', 'connections_languages'),
				'fil' => __( 'Filipino', 'connections_languages'),
				'fra' => __( 'French', 'connections_languages'),
				'ger' => __( 'German', 'connections_languages'),
				'hin' => __( 'Hindi', 'connections_languages'),
				'ind' => __( 'Indonesian', 'connections_languages'),
				'ita' => __( 'Italian', 'connections_languages'),
				'jpn' => __( 'Japanese', 'connections_languages'),
				'kor' => __( 'Korean', 'connections_languages'),
				'por' => __( 'Portuguese', 'connections_languages'),
				'rus' => __( 'Russian', 'connections_languages'),
				'slv' => __( 'Slovenian', 'connections_languages'),
				'spa' => __( 'Spanish', 'connections_languages'),
				'tai' => __( 'Tai-Kadai', 'connections_languages'),
				'vie' => __( 'Vietnamese', 'connections_languages'),
			);

			return apply_filters( 'cn_languages_options', $options );
		}

		/**
		 * Return the language based on the supplied key (ISO 639-2, the alpha-3 code).
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 * @uses   levels()
		 * @param  string $code  The key of the language to return.
		 * @return mixed         bool | string	The language if found, if not, FALSE.
		 */
		private static function language( $code = '' ) {

			if ( ! is_string( $code ) || empty( $code ) || $code === '-1' ) {

				return FALSE;
			}

			$languages = self::languages();
			$language  = isset( $languages[ $code ] ) ? $languages[ $code ] : FALSE;

			return $language;
		}

		/**
		 * Callback for the `cn_csv_export_fields_config` filter.
		 *
		 * @access private
		 * @since  2.0
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		public static function registerCustomFieldCSVExportConfig( $fields ) {

			$fields[] = array(
				'field'  => 'languages',
				'type'   => 'languages',
				'fields' => '',
				'table'  => CN_ENTRY_TABLE_META,
				'types'  => NULL,
			);

			return $fields;
		}

		/**
		 * Callback for the `cn_export_header-languages` action.
		 *
		 * @access private
		 *
		 * @param string                 $header
		 * @param array                  $field
		 * @param cnCSV_Batch_Export_All $export
		 *
		 * @return string
		 * @since  2.0
		 *
		 */
		public static function registerCSVExportFieldHeader( $header, $field, $export ) {

			$header = __( 'Languages', 'connections_languages' );

			return $header;
		}

		/**
		 * Callback for the `cn_export_field-languages` filter.
		 *
		 * @access private
		 * @since  2.0
		 *
		 * @param string                 $value
		 * @param object                 $entry
		 * @param array                  $field The field config array.
		 * @param cnCSV_Batch_Export_All $export
		 *
		 * @return string
		 */
		public static function registerCustomFieldExportAction( $value, $entry, $field, $export ) {

			if ( 'languages' !== $field['field'] ) return $value;

			$value = '';
			$meta  = cnMeta::get( 'entry', $entry->id, $field['field'], TRUE );

			if ( ! empty( $meta ) ) {

				$languages = array();

				foreach ( $meta as $code ) {

					$languages[] = self::language( $code );
				}

				if ( 0 < count( $languages ) ) {

					$value = $export->escapeAndQuote( implode( ', ', $languages ) );
				}
			}

			return $value;
		}

		/**
		 * Callback for the `cncsv_map_import_fields` filter.
		 *
		 * @access private
		 * @since  2.0
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		public static function registerCSVImportFieldHeader( $fields ) {

			$fields['languages'] = __( 'Languages', 'connections_languages' );

			return $fields;
		}

		/**
		 * Callback for the `cncsv_import_fields` action.
		 *
		 * @access private
		 * @since  2.0
		 *
		 * @param int         $id
		 * @param array       $row
		 * @param cnCSV_Entry $entry
		 */
		public static function registerCustomFieldImportAction( $id, $row, $entry ) {

			$meta = array();
			$data = $entry->arrayPull( $row, 'languages' );

			if ( ! is_null( $data ) ) {

				$languages = explode( ',', $data );
				$codes     = array();

				if ( 0 < count( $languages ) ) {

					foreach ( $languages as $language ) {

						$result = array_search( trim( $language ), self::languages() );

						if ( FALSE !== $result ) {

							$codes[] = $result;
						}
					}
				}

				if ( 0 < count( $codes ) ) {

					$meta[] = array(
						'key'   => 'languages',
						'value' => $codes,
					);

					cnEntry_Action::meta( 'update', $id, $meta );
				}
			}
		}

		/**
		 * Registered the custom metabox.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 * @uses   levels()
		 * @uses   cnMetaboxAPI::add()
		 * @return void
		 */
		public static function registerMetabox() {

			$atts = array(
				'name'     => __( 'Languages', 'connections_languages' ),
				'id'       => 'languages',
				'title'    => __( 'Languages', 'connections_languages' ),
				'context'  => 'side',
				'priority' => 'core',
				'fields'   => array(
					array(
						'id'      => 'languages',
						'type'    => 'checkboxgroup',
						'options' => self::languages(),
						'default' => '',
						),
					),
				);

			cnMetaboxAPI::add( $atts );
		}

		/**
		 * Add the custom meta as an option in the content block settings in the admin.
		 * This is required for the output to be rendered by $entry->getContentBlock().
		 *
		 * @access private
		 * @since  1.0
		 * @param  array  $blocks An associtive array containing the registered content block settings options.
		 * @return array
		 */
		public static function settingsOption( $blocks ) {

			$blocks['languages'] = __( 'Languages', 'connections_languages' );

			return $blocks;
		}

		/**
		 * Callback for the `cn_output_meta_field-languages` action.
		 *
		 * Renders the Languages content block.
		 *
		 * @internal
		 * @since 1.0
		 *
		 * @param string       $id     The field id.
		 * @param array        $value  The language codes (ISO 639-2, the alpha-3 code).
		 * @param cnEntry_HTML $object An instance of the cnEntry object.
		 * @param array        $atts   The shortcode atts array passed from the calling action.
		 */
		public static function block( $id, $value, $object, $atts ) {

			echo '<ul class="cn-languages">';

			foreach ( $value as $code ) {

				if ( $language = self::language( $code ) ) {

					printf( '<li class="cn-language cn-%1$s">%2$s</li>', esc_attr( $code ), esc_html( $language ) );
				}

			}

			echo '</ul>';
		}

	}

	/**
	 * Start up the extension.
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @return Connections_Languages|false
	 */
	function Connections_Languages() {

		if ( class_exists( 'connectionsLoad' ) ) {

			return Connections_Languages::instance();

		} else {

			add_action(
				'admin_notices',
				function() {
					echo '<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Languages.</p></div>';
				}
			);

			return false;
		}
	}

	/**
	 * Since Connections loads at default priority 10, and this extension is dependent on Connections,
	 * we'll load with priority 11 so we know Connections will be loaded and ready first.
	 */
	add_action( 'plugins_loaded', 'Connections_Languages', 11 );

}
