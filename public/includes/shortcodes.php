<?php
add_shortcode( 'ucf-input', 'ucf_input_field' );
/**
 * Generate a form input
 */
function ucf_input_field( $atts ) {

	$shortcode = new Contact_Form_Generator();

	extract( shortcode_atts( array(
		'id' 		  => false,
		'name' 		  => false,
		'required' 	  => false,
		'pattern' 	  => false,
		'placeholder' => false,
		'autofocus'   => false,
		'title' 	  => false,
		'min' 		  => false, 	// Date & number inputs only
		'max' 		  => false, 	// Date & number inputs only
		'multiple' 	  => false, 	// For input files only
		'step' 		  => false,

	), $atts ) );

}

add_shortcode( 'ucf-form', 'ucf_form' );
/**
 * [ucf_form description]
 * @param  [type] $atts [description]
 * @return [type]       [description]
 */
function ucf_form( $atts ) {

	extract( shortcode_atts( array(
		'id' 		  => false,
		'name' 		  => false,
		'required' 	  => false,
		'pattern' 	  => false,
		'placeholder' => false,
		'autofocus'   => false,
		'title' 	  => false,
		'min' 		  => false, 	// Date & number inputs only
		'max' 		  => false, 	// Date & number inputs only
		'multiple' 	  => false, 	// For input files only
		'step' 		  => false,

	), $atts ) );

	if( !$id )
		return;

	/* Instanciate a new form */
	$form = new Contact_Form_Generator( $id );

	return $form->generate_form();

}