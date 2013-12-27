<?php
/**
 * Universal Contact Form.
 *
 * @package   Universal_Contact_Form
 * @author    ThemeAvenue <hello@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2013 ThemeAvenue
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * @package Universal_Contact_Form
 * @author  Julien Liabeuf <julien@liabeuf.Fr>
 */
class Universal_Contact_Form_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = Universal_Contact_Form::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		// add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		// Register post types
		$form = new TAV_Custom_Post_Type( __( 'Form', 'ucf' ), array( 'supports' => array( 'title', 'editor' ) ) );

		add_action( 'add_meta_boxes', array( $this, 'form_settings_metabox' ) );
		add_action( 'save_post', array( $this, 'save_post_meta' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @TODO:
	 *
	 * - Rename "Plugin_Name" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Plugin_Name::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @TODO:
	 *
	 * - Rename "Plugin_Name" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Plugin_Name::VERSION );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 * @TODO:
		 *
		 * - Change 'Page Title' to the title of your plugin admin page
		 * - Change 'Menu Text' to the text for menu item for the plugin settings page
		 * - Change 'manage_options' to the capability you see fit
		 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Page Title', $this->plugin_slug ),
			__( 'Menu Text', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	public function form_settings_metabox() {

		add_meta_box( 'ucf_form_settings', __( 'Form Settings', 'ucf' ), array( $this, 'form_settings_metabox_content'), 'form', 'side', 'default' );

	}

	public function form_settings_metabox_content() {

		// Get post ID
		$pid = isset( $_GET['post'] ) ? $_GET['post'] : '';

		// Get meta values
		$layout 		= get_post_meta( $pid, '_ucf_layout', true );
		$labels 		= get_post_meta( $pid, '_ucf_labels', true );
		$autocomplete 	= get_post_meta( $pid, '_ucf_autocomplete', true );
		$validate 		= get_post_meta( $pid, '_ucf_validate', true );
		$class 			= get_post_meta( $pid, '_ucf_form_class', true );
		?>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<td>
						<label for="ucf_layout"><strong><?php _e( 'Layout', 'ucf' ); ?></strong></label>
						<select name="ucf_layout" id="ucf_layout">
							<option value="" <?php if( '' == $layout ) { echo 'selected="selected"'; } ?>><?php _e( 'Normal', 'ucf' ); ?></option>
							<option value="inline" <?php if( 'inline' == $layout ) { echo 'selected="selected"'; } ?>><?php _e( 'Inline', 'ucf' ); ?></option>
							<option value="horizontal" <?php if( 'horizontal' == $layout ) { echo 'selected="selected"'; } ?>><?php _e( 'Horizontal', 'ucf' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<td>
						<label for="ucf_labels"><strong><?php _e( 'Show Labels', 'ucf' ); ?></strong></label>
						<select name="ucf_labels" id="ucf_labels">
							<option value="yes" <?php if( 'yes' == $labels ) { echo 'selected="selected"'; } ?>><?php _e( 'Yes', 'ucf' ); ?></option>
							<option value="no" <?php if( 'no' == $labels ) { echo 'selected="selected"'; } ?>><?php _e( 'No', 'ucf' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<td>
						<label for="ucf_autocomplete"><strong><?php _e( 'Autocomplete', 'ucf' ); ?></strong></label>
						<select name="ucf_autocomplete" id="ucf_autocomplete">
							<option value="on" <?php if( 'on' == $autocomplete ) { echo 'selected="selected"'; } ?>><?php _e( 'On', 'ucf' ); ?></option>
							<option value="off" <?php if( 'off' == $autocomplete ) { echo 'selected="selected"'; } ?>><?php _e( 'Off', 'ucf' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<td>
						<label for="ucf_validate"><strong><?php _e( 'Validate', 'ucf' ); ?></strong></label>
						<select name="ucf_validate" id="ucf_validate">
							<option value="yes" <?php if( 'yes' == $validate ) { echo 'selected="selected"'; } ?>><?php _e( 'Yes', 'ucf' ); ?></option>
							<option value="no" <?php if( 'no' == $validate ) { echo 'selected="selected"'; } ?>><?php _e( 'No', 'ucf' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<td>
						<label for="ucf_form_class"><strong><?php _e( 'Form Extra Class(es)', 'ucf' ); ?></strong></label>
						<input type="text" id="ucf_form_class" name="ucf_form_class" value="<?php echo $class; ?>">
					</td>
				</tr>
			</tbody>
			<?php wp_nonce_field( 'save_ucf_metas', 'ucf_metas', false, true ); ?>
		</table>

	<?php }

	function save_post_meta( $post_id ) {

		/*
		* We need to verify this came from the our screen and with proper authorization,
		* because save_post can be triggered at other times.
		*/

		// Check if our nonce is set.
		if ( ! isset( $_POST['ucf_metas'] ) )
			return $post_id;

		$nonce = $_POST['ucf_metas'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'save_ucf_metas' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'form' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		}

		/* OK, its safe for us to save the data now. */

		$data = array(
			'ucf_layout',
			'ucf_autocomplete',
			'ucf_validate',
			'ucf_form_class',
			'ucf_labels',
		);

		foreach( $data as $field ) {

			$old = get_post_meta( $post_id, "_$field", true );
			$key = "_$field";
			$val = sanitize_text_field( $_POST[$field] );

			/* No previous value found */
			if( '' == $old ) {

				if( isset( $_POST[$field] ) && '' != $_POST[$field] )
					update_post_meta( $post_id, $key, $val );

			}

			/* Previous value found */
			else {

				if( !isset( $_POST[$field] ) || isset( $_POST[$field] ) && '' == $_POST[$field] )
					delete_post_meta( $post_id, $key, $old );

				else
					update_post_meta( $post_id, $key, $val, $old );

			}

			$field = sanitize_text_field( $_POST[$field] );

		}

	}

}
