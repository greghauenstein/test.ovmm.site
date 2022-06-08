<?php
/**
 * Gravity Forms functions
 */

/**
 * Gravity Forms jQuery
 *
 * Gravity Forms requires jQuery so queue it up when a form is used, also
 * enqueues the datepicker since it is used frequently
 */
function jb_gravity_forms_jquery( $form ) {
	wp_enqueue_script( 'jquery' );

	wp_enqueue_style(
		'jb-ui-datepicker',
		JB_CORE_URL . '/datepicker/jquery-ui-1.10.3.custom.min.css'
	);

	return $form;
}
add_filter( 'gform_pre_render', 'jb_gravity_forms_jquery' );


// There are multiple place where gravity forms does pre-rendering for forms
// this returns an array to loop through b/c I am lazy.
function jb_gravity_forms_render_filters() {
	return [
		'gform_pre_render',
		'gform_pre_validation',
		'gform_pre_validation',
		'gform_admin_pre_render',
 	];
}


// Populate a checkbox field from a taxonomy
// Returns the choices and inputs for the checkbox field
function jb_gravity_forms_populate_checkboxes( $field_id, $taxonomy ) {
	$checkboxes = [
		'choices' => [],
		'inputs'  => [],
	];

	// Query the list of terms
	$terms = get_terms([
		'taxonomy'   => $taxonomy,
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC'
	]);

	// Set up the counter for the checkboxes which
	// needs to start at on for GF
	$input_id = 1;

	foreach ( $terms as $term ):

		// Skipping index that are multiples of 10
		// (multiples of 10 create problems as the input IDs)
		// h/t: https://docs.gravityforms.com/gform_pre_render/#2-populate-choices-checkboxes
		if ( $input_id % 10 == 0 ) {
			$input_id++;
		}

		$checkboxes['choices'][] = [
			'text'  => $term->name,
			'value' => $term->term_id,
		];

		$checkboxes['inputs'][] = [
			'id'    => "{$field_id}.{$input_id}",
			'label' => $term->name,
		];

		// Increment the counter
		$input_id++;
	endforeach;

	return $checkboxes;
}
