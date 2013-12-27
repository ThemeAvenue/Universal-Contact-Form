<?php
/**
 * Contact Form Generator.
 *
 * @package   Universal Contact Form
 * @author    ThemeAvenue <hello@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2013 ThemeAvenue
 */

/**
 * Generate the contact form
 *
 * @package Universal Contact Form
 * @author  Julien Liabeuf <julien@liabeuf.fr>
 * @version 1.0.0
 */
class Contact_Form_Generator {

	public function __construct( $id ) {

		/* Start the session if needed */
		if( !session_id() )
			session_start();

		/**
		 * Current instance's form ID
		 *
		 * @since 1.0.0
		 * @var (boolean)
		 */
		$this->form_id = $id;

		/**
		 * Get form fields
		 *
		 * @since 1.0.0
		 */
		$this->tags = $this->get_fields();

		/**
		 * Get form settings
		 *
		 * @since 1.0.0
		 */
		$this->layout 			= get_post_meta( $id, '_ucf_layout', true );
		$this->autocomplete 	= get_post_meta( $id, '_ucf_autocomplete', true );
		$this->validate 		= get_post_meta( $id, '_ucf_validate', true );
		$this->class 			= get_post_meta( $id, '_ucf_form_class', true );

	}

	public function get_fields() {

		$post = get_post( $this->form_id );

		if( null == $post )
			return;

		/* Find the tags */
		preg_match_all( '/{{(.*?)}}/s', $post->post_content, $matches );

		return $matches[1];

	}

	/**
	 * List allowed input arguments
	 *
	 * Input arguments are the varbs that can be added to the <input> HTML tag.
	 *
	 * @since 1.0.0
	 * @return (array) Allowed arguments
	 */
	public function get_field_arguments() {

		$allowed = array(
			'id'				=> false,
			'name'				=> false,
			'required'			=> false,
			'pattern'			=> false,	// For regex validation
			'placeholder'		=> false,
			'autofocus'			=> false,
			'title'				=> false,
			'min'				=> false, 	// Date & number inputs only
			'max'				=> false, 	// Date & number inputs only
			'multiple'			=> false, 	// For input files only
			'step'				=> false,
			'rows' 				=> false,		// For textarea
		);

		/**
		 * ucf_allowed_arguments hook
		 *
		 * Let the user customize the list of allowed arguments
		 */
		return apply_filters( 'ucf_allowed_arguments', $allowed );

	}

	/**
	 * List allowed parameters
	 *
	 * Parameters are the field global parameters, which includes container
	 * parameters.
	 *
	 * @since 1.0.0
	 * @return (array) Allowed parameters
	 */
	public function get_field_parameters() {

		$params = array(
			'class'				=> 'form-control',
			'class_container' 	=> array( 'form-group' ),
			'class_label' 		=> false,
			'label' 			=> '',
			'hide_label' 		=> false
		);

		/**
		 * ucf_allowed_parameters hook
		 *
		 * Let the user customize the list of allowed parameters
		 */
		return apply_filters( 'ucf_allowed_parameters', $params );

	}

	/**
	 * A merge of field arguments and parameters
	 *
	 * This will be used to sanitize the arguments passed
	 * in the shortcode.
	 * 
	 * @since 1.0.0
	 * @return (array) Allowed arguments
	 */
	public function merge_args_params() {

		$args   = $this->get_field_arguments();
		$params = $this->get_field_parameters();

		return array_merge( $args, $params );

	}

	/**
	 * Generate the contact form
	 *
	 * This method generates the actual contact form
	 * with all registered fields.
	 *
	 * @since 1.0.0
	 * @return (string) HTML form
	 */
	public function generate_form() {

		$fields  = $this->get_form_fields();
		$args    = $this->get_field_arguments();
		$params  = $this->get_field_parameters();
		$allowed = $this->merge_args_params();
		$output  = '';

		/**
		 * Iterate through the form fields
		 */
		foreach( $fields as $field ) {

			/* Reset verbs */
			$verbs = array();

			/* Reset the temp markup */
			$temp = '';

			/* Get field type */
			$type = $field['type'];

			/* Get field name */
			$name = $field['args']['name'];

			/* Prepare field callback function */
			$callback = "ucf_get_field_$type";

			/* Merge defaults args with user args */
			$verbs = array_merge( $allowed, $field['args'] );

			/* Make the label class string an array */
			$verbs['class_label'] = array( $verbs['class_label'] );

			/* No label class */
			$nolabel = apply_filters( 'ucf_field_label_hidden_class', 'sr-only' );

			/* In case there was a submission failure due to required fields, we add the error class */
			if( isset( $_SESSION['ucf_errors'] ) && in_array( $name, $_SESSION['ucf_errors'] ) )
				array_push( $verbs['class_container'], apply_filters( 'ucf_missing_field_class', 'has-error' ) );

			/**
			 * Let's double check the callback function actually exists
			 */
			if( !function_exists( $callback ) )
				return;

			/**
			 * Clean the verbs
			 */
			foreach( $verbs as $verb => $value ) {

				/* If the user wants to hide the label, we apply the Bootstrat 3 class */
				if( 'hide_label' == $verb ) {

					if( $value )
						array_push( $verbs['class_label'], $nolabel );

				}

				/* If no label is set, we use the field name */
				if( 'label' == $verb && '' == $value ) {

					$verbs['label'] = ucwords( $verbs['name'] );
					continue;

				}

				/* Delete all verbs with false value */
				if( !$value ) {
					unset( $verbs[$verb] );
					unset( $args[$verb] );
				}

				if( is_array( $value ) && 'class_label' != $verb )
					$verbs[$verb] = implode( ' ', $value );

			}

			/**
			 * Add fallback to the field ID
			 */
			if( !isset( $verbs['id'] ) ) {

				if( isset( $verbs['name'] ) )
					$verbs['id'] = $verbs['name'];

			}

			/**
			 * Hide the label if the form is configured with no labels
			 */
			if( 'no' == get_post_meta( $this->form_id, '_ucf_labels', true ) && !in_array( $nolabel, $verbs['class_label'] ) )
				array_push( $verbs['class_label'], $nolabel );

			if( 'horizontal' == get_post_meta( $this->form_id, '_ucf_layout', true ) )
				array_push( $verbs['class_label'], 'col-sm-2 control-label' );

			/* Now we merge the array and add the correct syntax */
			$verbs['class_label'] = array_filter( $verbs['class_label'] );
			if( !empty( $verbs['class_label'] ) ) {
				$verbs['class_label'] = 'class="' . implode( ' ', $verbs['class_label'] ) . '"';
			} else {
				$verbs['class_label'] = '';
			}

			/**
			 * Prepare input arguments
			 */
			$new = array();

			foreach( $verbs as $arg => $value ) {

				if( array_key_exists( $arg, $params ) )
					continue;

				if( $value && is_bool( $value ) )
					array_push( $new, $arg );
				else
					array_push( $new, "$arg='$value'" );

			}

			/* List input arguments as a string */
			$new = implode( ' ', $new );

			/* We don't want to display the HTML straight away */
			ob_start();

			/* Get preliminary markup */
			$temp = $callback( $field );

			/* Let the user customize the markup */
			$temp = apply_filters( "ucf_markup_field_$type", $temp );

			/* Get the HTML content */
			$temp = ob_get_contents();

			ob_end_clean();

			/* In case we have form horizontal, modify the markup */
			if( 'horizontal' == get_post_meta( $this->form_id, '_ucf_layout', true ) ) {

				preg_match_all( '/<input.*>/imU', $temp, $matches );

				if( isset( $matches[0][0] ) ) {

					$input 		= $matches[0][0];
					$horizontal = apply_filters( 'ucf_form_horizontal_class', 'col-sm-10' );
					$temp 		= str_replace( $matches[0][0], "<div class='$horizontal'>$input</div>", $temp );

				}

			}

			/**
			 * EXPERIMENTAL
			 *
			 * Replace default values in the template by user custom
			 */
			$pattern = "(\S+)=(\"|'| |)(.*)(\"|'| |>)";

			/* Extract HTML tags/values */
			preg_match_all( "@$pattern@isU", $temp, $tags );

			$replace = array(
				'placeholder',
				'pattern',
				'title'
			);

			$c = 0; // Counter

			/* Parse all HTML tags */
			foreach( $tags[1] as $k => $tag ) {

				/* Replace value by user custom if needed */
				if( in_array( $tag, $replace ) && isset( $verbs[$tag] ) )
					$temp = str_replace( $tags[3][$c], $verbs[$tag], $temp );

				$c++; // Increment count

			}

			/* Replace HTML tags values */
			foreach( $verbs as $verb => $value ) {

				$temp = str_replace( '{' . $verb . '}', $value, $temp );

			}

			/* Add input arguments */
			$temp = str_replace( '{args}', $new, $temp );

			/**
			 * Add the possible value
			 *
			 * If the form fails to send (for instance all required fields
			 * were not populated), we automatically populate the fields with
			 * the values previously submitted.
			 */
			if( isset( $_SESSION['ucf_values'][$name] ) ) {

				$temp = str_replace( '{value}', $_SESSION['ucf_values'][$name]['value'], $temp );

			} else {
				$temp = str_replace( '{value}', '', $temp );
			}

			/* Append the new field to the overall markup */
			$output .= $temp;

		}

		/**
		 * Prepare the form attributes
		 */
		
		/* All attributes will be stored in this array */
		$attr = array();

		/**
		 * Prepare the possible for class
		 */
		$class = array();

		/* Add the class matching the selected layout */
		if( '' != $this->layout )
			array_push( $class, "form-$this->layout" );

		/* Add the possible extra class */
		if( '' != $this->class )
			array_push( $class, $this->class );

		array_push( $attr, 'class="' . implode( ' ', $class ) . '"' );

		/**
		 * Add the autocomplete on/off
		 */
		array_push( $attr, "autocomplete='$this->autocomplete'" );

		/**
		 * Enable / disable client side validation
		 */
		if( 'no' == $this->validate )
			array_push( $attr, 'novalidate' );

		/**
		 * Add the form ID
		 */
		array_push( $attr, "id='ucf-form-$this->form_id'");

		/**
		 * Add form role
		 */
		array_push( $attr, 'role="form"' );

		/**
		 * Implode all the attributes
		 */
		$attr = implode( ' ', $attr );

		/**
		 * We clean the session. However, if the mail
		 * was not sent for whatever reason, we keep the
		 * data in order to let the user re-try sending.
		 */
		if( isset( $_GET['send'] ) && 'false' != $_GET['send'] ) {

			if( isset( $_SESSION['ucf_values'] ) )
				unset( $_SESSION['ucf_values'] );

			if( isset( $_SESSION['ucf_errors'] ) )
				unset( $_SESSION['ucf_errors'] );

		}

		return sprintf( $this->get_form_markup(), $attr, $output );

	}

	function get_form_markup() {

		global $post;

		$form_id 	= $this->form_id;
		$pid 		= $post->ID;
		$nonce 		= wp_nonce_field( "submit_form_$form_id", 'ucf_submission', false, false );
		$btn_class 	= apply_filters( 'ucf_submit_button_class', 'btn btn-default', $this->form_id );
		$action 	= get_permalink( $post->ID );
		$form 		= '';

		/**
		 * Handle notifications
		 */
		if( isset( $_GET['send'] ) && isset( $_GET['fid'] ) && $form_id == $_GET['fid'] ) {

			switch( $_GET['send'] ):

				case 'true':

					$message = apply_filters( 'ucf_send_success', __( 'Your message has been sent successfully.', 'ucf' ) );
					$markup  = sprintf( apply_filters( 'ucf_email_sent_markup', '<div class="alert alert-success">%s</div>' ), $message );

				break;

				case 'false':

					$message = apply_filters( 'ucf_send_fail', __( 'An error occured while trying to send your message. Please try again later.', 'ucf' ) );
					$markup  = sprintf( apply_filters( 'ucf_email_sent_failed_markup', '<div class="alert alert-danger">%s</div>' ), $message );

				break;

				case 'missing':

					$message = apply_filters( 'ucf_send_missing', __( 'You didn\'t fill the form correctly. Please check the highlighted fields.', 'ucf' ) );
					$markup  = sprintf( apply_filters( 'ucf_email_sent_failed_markup', '<div class="alert alert-warning">%s</div>' ), $message );

				break;

			endswitch;

			$form .= $markup;

		}

		$form .= '<form method="post" action="' . $action . '" %s>
			%s
			' . $nonce . '
			<input type="hidden" name="form_id" value="' . $form_id . '">
			<input type="hidden" name="pid" value="' . $pid . '">
			<button type="submit" class="' . $btn_class . '">' . __( 'Submit', 'ucf' ) . '</button>
		</form>';

		return apply_filters( 'ucf_form_markup', $form );

	}

	/**
	 * Get form fields
	 *
	 * Get all the fields registered in this form. The returned fields
	 * are sanitized based on the available callback functions.
	 *
	 * To see the available callback function, check /public/view/templates/php
	 *
	 * @since 1.0.0
	 * @return (array) Fields list with their arguments
	 */
	public function get_form_fields() {

		$tags = $this->tags;

		if( !is_array( $tags ) )
			return;

		/* We will store all form fields and their attributes in this array */
		$form_fields = array();

		foreach( $tags as $key => $tag ) {

			$matches = array();
			$string  = $tag;
			$args 	 = array();

			/* Extract the field type and arguments from the tag */
			preg_match_all('#([a-zA-Z0-9]+)="([^"]+)"#', $string, $matches);

			/* Remove the key/value pairs from the original string */
			foreach( $matches[0] as $key => $match ) {
				$string = str_replace( $match, '', $string );
			}

			/* Prepare the args key/value pairs */
			$count = 0;
			foreach( $matches[1] as $key => $match ) {

				$args[$match] = $matches[2][$count];
				$count++;

			}

			/* Get the non pair values (including field type) */
			$string = explode( ' ', $string );

			/* Get field type */
			$type = $string[0];

			/* Remove type from array */
			unset( $string[0] );

			/* Clean the array (remove empty pairs) */
			$string = array_filter( $string );

			/* Get the non pair arguments and set them to true */
			if( !empty( $string ) ) {
				foreach( $string as $key => $value ) {
					$args[$value] = true;
				}
			}

			/* Prepare new field */
			$input = array( 'type' => $type, 'args' => $args );

			/* Sanitize arguments */
			$input = $this->filter_allowed_arguments( $input );

			/* Add the field to the complete list */
			array_push( $form_fields, $input );

		}

		/* Return sanitized input list */
		return $this->filter_allowed_fields( $form_fields );

	}

	/**
	 * Sanitize the fields
	 *
	 * This function checks if there is an associated
	 * callback function for the field type that is being
	 * tested.
	 *
	 * @since 1.0.0
	 * @param  (array) $fields List of fields to test
	 * @return (array)         Clean list of form fields
	 */
	public function filter_allowed_fields( $fields ) {

		foreach( $fields as $key => $field ) {

			$type = $field['type'];

			if( !function_exists( "ucf_get_field_$type" ) ) {

				unset( $fields[$key] );

			}

		}

		return $fields;

	}

	/**
	 * Sanitize the field arguments
	 *
	 * Check the field registered arguments against the list
	 * of allowed arguments.
	 *
	 * @since 1.0.0
	 * @param  (array) $field Field to test
	 * @return (array)        Field with cleaned arguments
	 */
	public function filter_allowed_arguments( $field ) {

		$allowed = $this->merge_args_params();

		foreach( $field['args'] as $arg => $value ) {

			if( !array_key_exists( $arg, $allowed ) ) {

				unset( $field['args'][$arg] );

			}

		}

		return $field;

	}

}