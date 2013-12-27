<?php
function ucf_get_field_text( $field ) { ?>

	<div class="{class_container}">
		<label for="{id}" {class_label}>{label}</label>
		<input type="text" class="{class}" id="{id}" {args} {value}>
	</div>

<?php }
function ucf_get_field_email( $field ) { ?>

	<div class="{class_container}">
		<label for="{id}" {class_label}>{label}</label>
		<input type="email" class="{class}" id="{id}" {args} {value}>
	</div>

<?php }
function ucf_get_field_url( $field ) { ?>

	<div class="{class_container}">
		<label for="{id}" {class_label}>{label}</label>
		<input type="url" class="{class}" id="{id}" {args} {value} placeholder="http://">
	</div>

<?php }
function ucf_get_field_textarea( $field ) { ?>

	<div class="{class_container}">
		<label for="{id}" {class_label}>{label}</label>
		<textarea class="{class}" id="{id}" {args}>{value}</textarea>
	</div>

<?php }
function ucf_get_field_select( $field ) { ?>

	<div class="{class_container}">
		<label for="{id}" {class_label}>{label}</label>
		<select class="{class}">
			<option>1</option>
			<option>2</option>
			<option>3</option>
			<option>4</option>
			<option>5</option>
		</select>
	</div>

<?php }
function ucf_get_field_selectmultiple( $field ) { ?>

	<div class="{class_container}">
		<label for="{id}" {class_label}>{label}</label>
		<select class="{class}" multiple>
			<option>1</option>
			<option>2</option>
			<option>3</option>
			<option>4</option>
			<option>5</option>
		</select>
	</div>

<?php }
function ucf_get_field_date( $field ) { ?>

	<div class="{class_container}">
		<label for="{id}" {class_label}>{label}</label>
		<input type="date" class="{class}" id="{id}" {args} {value}>
	</div>

<?php }
function ucf_get_field_hexcolor( $field ) { ?>

	<div class="{class_container}">
		<label for="{id}" {class_label}>{label}</label>
		<input type="text" class="{class}" id="{id}" pattern="^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" title="Please enter a valid hexadecimal code. Format is #CCC or #CCCCCC" {args} {value}>
	</div>

<?php }
function ucf_get_field_creditcard( $field ) { ?>

	<div class="{class_container}">
		<label for="{id}" {class_label}>{label}</label>
		<input type="text" class="{class}" id="{id}" pattern="^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" title="Please enter a valid Credit Card Number" {args} {value}>
	</div>

<?php }
function ucf_get_field_creditcarddamex( $field ) { ?>

	<div class="{class_container}">
		<label for="{id}" {class_label}>{label}</label>
		<input type="text" class="{class}" id="{id}" pattern="[0-9]{4} *[0-9]{6} *[0-9]{5}" title="Please enter a Amex Credit Card Number" {args} {value}>
	</div>

<?php }