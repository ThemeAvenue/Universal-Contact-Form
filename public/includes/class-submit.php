<?php
class UCF_Submit {

	/**
	 * Single instance of the class
	 */
	protected static $instance = null;

	public function __construct() {

		/**
		 * Make sure we have the mendatory data
		 */
		if( isset( $_POST['form_id'] ) && isset( $_POST['ucf_submission'] ) ) {

			/**
			 * Form nonce
			 * 
			 * @var (string)
			 */
			$this->nonce = sanitize_key( $_POST['ucf_submission'] );

			/**
			 * Form ID
			 * 
			 * @var (integer)
			 */
			$this->form_id = intval( $_POST['form_id'] );

			/**
			 * Sanitization methods
			 *
			 * @var (array)
			 */
			$this->sanitization = array();

			/**
			 * List of possibly missing fields
			 *
			 * @var (array)
			 */
			$this->missing = array();

			/**
			 * Validate the nonce before going any further
			 */
			if( !wp_verify_nonce( $this->nonce, "submit_form_$this->form_id" ) )
				return;

			add_action( 'wp_loaded', array( $this, 'add_base_sanitization' ) );
			add_action( 'wp_loaded', array( $this, 'process_submission' ) );

		}

	}

	/**
	 * Load a single instance of this class if
	 * the current instance is null
	 * 
	 * @return (object) Class instance
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Orchestrate the submission process
	 *
	 * @since 1.0.0
	 */
	public function process_submission() {

		/* Get the form values and validate them */
		$form_fields = $this->check_fields();

		/* Prepare read only redirection */
		$redirect = add_query_arg( array( 'send' => 'true', 'fid' => $this->form_id ), get_permalink( $_POST['pid'] ) );

		/* If all required fields aren't submitted, go back to the form */
		if( !empty( $this->missing ) ) {

			$this->save_submitted_values();
			$redirect = add_query_arg( array( 'send' => 'missing', 'fid' => $this->form_id ), get_permalink( $_POST['pid'] ) );

		} else {

			/* Build the e-mail and send */
			$send = $this->send_mail();

			/* Check if e-mail was sent */
			if( !$send )
				$redirect = add_query_arg( array( 'send' => 'false', 'fid' => $this->form_id ), get_permalink( $_POST['pid'] ) );

		}

		/* Read only redirect */
		wp_redirect( $redirect );
		exit;

	}

	/**
	 * Get the list of sanitization functions
	 *
	 * @since 1.0.0
	 * @return (array) Sanitization functions
	 */
	public function get_sanitization_method() {

		return $this->sanitization;

	}

	/**
	 * Add a new sanitization method
	 *
	 * @since 1.0.0
	 * @param (string) $field_type   Type of field to sanitize
	 * @param (string) $sanitization Callback function for sanitization
	 */
	public function add_sanitization_method( $field_type, $sanitization ) {

		if( function_exists( $sanitization ) )
			$this->sanitization[$field_type] = $sanitization;

	}

	/**
	 * Add sanitization callbacks for built-in field types
	 *
	 * @since 1.0.0
	 */
	public function add_base_sanitization() {

		$this->add_sanitization_method( 'text', 'sanitize_text_field' );
		$this->add_sanitization_method( 'email', 'sanitize_email' );
		$this->add_sanitization_method( 'url', 'esc_url' );
		$this->add_sanitization_method( 'hexcolor', 'sanitize_hex_color' );

	}

	/**
	 * Get a sanitized array of field/value pair
	 *
	 * @since 1.0.0
	 * @return (array) Fields to submit
	 */
	public function check_fields() {

		$form 		 = new Contact_Form_Generator( $this->form_id );
		$form_fields = $form->get_form_fields();
		$cleanpost   = array();

		foreach( $form_fields as $key => $field ) {

			/* Get field info */
			$type  = $field['type'];
			$name  = $field['args']['name'];

			/* Check if field is required */
			if( $field['args']['required'] && ( !isset( $_POST[$name] ) || '' == $_POST[$name] ) ) {

				$this->add_missing_field( $name );
				continue;

			}

			/* Get sanitization function */
			$sanitize = $this->get_sanitization_method();

			/* Sanitize value if possible */
			if( isset( $sanitize[$type] ) )
				$value = $sanitize[$type]( $_POST[$name] );
			else
				$value = sanitize_text_field( $_POST[$name] );

			/* Add the value to clean data */
			$label = isset( $field['args']['label'] ) ? $field['args']['label'] : ucwords( $name );
			$cleanpost[$name] = array( 'value' => $value, 'label' => $label );
		}

		$this->cleanpost = $cleanpost;

		return $cleanpost;

	}

	/**
	 * Set a new field as missing a value
	 *
	 * @since 1.0.0
	 * @param (string) $name Field name
	 */
	private function add_missing_field( $name ) {

		array_push( $this->missing, $name );

	}

	/**
	 * Save submitted values in session
	 *
	 * In case the server-side validation fails,
	 * we save the submitted values temporarily in order
	 * to automatically populate the fields when reloading
	 * the form.
	 *
	 * @since 1.0.0
	 */
	public function save_submitted_values() {

		/* Start the session if needed */
		if( !session_id() )
			session_start();

		$_SESSION['ucf_values'] = $this->check_fields();
		$_SESSION['ucf_errors'] = $this->missing;

	}

	/**
	 * Get e-mail template
	 *
	 * @since 1.0.0
	 * @return (string) E-mail template
	 */
	public function get_email_template() {

		/**
		 * No real template management for now, but this will come
		 * in the future. This function will allows to retrieve a template
		 * easily.
		 */
		$template = '{content}';

		return $template;

	}

	public function send_mail() {

		/* Get e-mail template */
		$template = $this->get_email_template();
		$content  = array();
		$form_fields = $this->cleanpost;

		foreach( $form_fields as $key => $value ) {

			array_push( $content, $value['label'] . ': ' . $value['value'] );

		}

		$content = implode( '<br>', $content );

		/* Insert content in the e-mail */
		$template = str_replace( '{content}', $content, $template );

		/* Get required values */
		$to 	  = get_bloginfo( 'admin_email' );
		$subject  = sprintf( __( 'E-mail from %s' ), get_bloginfo( 'name' ) );
		$sender   = isset( $form_fields['name']['value'] ) ? $form_fields['name']['value'] : get_bloginfo( 'name' );
		$email    = isset( $form_fields['email']['value'] ) ? $form_fields['email']['value'] : $to;

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: $sender <$email>" . "\r\n";

		/* Send the mail using WordPress pluggable function */
		$send = wp_mail( $to, $subject, $template, $headers );

		return $send;

	}

}