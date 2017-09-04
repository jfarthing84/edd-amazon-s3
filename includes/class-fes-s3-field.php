<?php

class FES_S3_Field extends FES_Field {
	/**
	 * Field Version.
	 *
	 * @access public
	 * @since  2.3
	 * @var    string
	 */
	public $version = '1.0.0';

	/**
	 * For 3rd parameter of get_post/user_meta.
	 *
	 * @access public
	 * @since  2.3
	 * @var    bool
	 */
	public $single = true;

	/**
	 * Supports are things that are the same for all fields of a field type.
	 * E.g. whether or not a field type supports jQuery Phoenix. Stored in object, not database.
	 *
	 * @access public
	 * @since  2.3
	 * @var    array
	 */
	public $supports = array(
		'multiple'    => false,
		'is_meta'     => true,
		'forms'       => array(
			'registration'     => false,
			'submission'       => true,
			'vendor-contact'   => false,
			'profile'          => false,
			'login'            => false,
		),
		'position'    => 'extension',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => true,
			'can_add_to_formbuilder'      => true,
		),
		'template'	  => 'edd_s3_upload',
		'title'       => 'Amazon S3 Upload',
		'phoenix'	   => false,
	);

	/**
	 * Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db.
	 *
	 * @access public
	 * @since  2.3
	 * @var    array
	 */
	public $characteristics = array(
		'name'        => '',
		'template'    => 'edd_s3_upload',
		'public'      => true,
		'required'    => false,
		'label'       => '',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
		'count'       => '1',
		'max_size'    => '',
		'extension'   => array(),
		'single'      => false,
	);

	/**
	 * Set the title of the field.
	 *
	 * @access public
	 * @since  2.3
	 */
	public function set_title() {
		$this->supports['title'] = apply_filters( 'fes_' . $this->name() . '_field_title', _x( 'Amazon S3 Upload', 'FES Field title translation', 'edd_s3' ) );
	}

	/**
	 * Returns the HTML to render a field in admin
	 *
	 * @access public
	 * @since  2.3
	 *
	 * @param  int    $save_id  Save ID.
	 * @param  bool   $readonly Is the field read only?
	 * @return string           HTML to render field in admin.
	 */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id   = apply_filters( 'fes_render_s3_upload_field_user_id_admin', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_s3_upload_field_readonly_admin', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_admin( $this->save_id, $user_id, $readonly );

		$uploaded_items = $value;
		if ( ! is_array( $uploaded_items ) || empty( $uploaded_items ) ) {
			$uploaded_items = array( 0 => '' );
		}

		$max_files = 0;
		if ( $this->characteristics['count'] > 0 ) {
			$max_files = $this->characteristics['count'];
		}

		$output  = '';
		$output .= sprintf( '<div class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output .= $this->label( $readonly );

		ob_start(); ?>
		<div class="fes-fields">
			<table class="<?php echo sanitize_key( $this->name() ); ?>">
				<thead>
					<tr>
						<td class="fes-s3-path" colspan="2"><?php _e( 'S3 Path', 'edd_s3' ); ?></td>
						<?php if ( fes_is_admin() ) { ?>
							<td class="fes-download-file"><?php _e( 'Download File', 'edd_s3' ); ?></td>
						<?php } ?>

						<?php if ( empty( $this->characteristics['single'] ) || $this->characteristics['single'] !== 'yes' ) { ?>
							 <td class="fes-remove-column">&nbsp;</td>
						<?php } ?>
					 </tr>
				</thead>
				<tbody class="fes-variations-list-<?php echo sanitize_key( $this->name() ); ?>">
					<input type="hidden" id="fes-upload-max-files-<?php echo sanitize_key( $this->name() ); ?>" value="<?php echo $max_files; ?>" />
					<?php foreach ( $uploaded_items as $index => $s3_file ) { ?>
					<tr class="fes-single-variation">
						<td class="fes-url-row">
							<input type="text" class="fes-file-value" data-formid="<?php echo $this->form;?>" data-fieldname="<?php echo $this->name();?>" name="<?php echo $this->name(); ?>[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $s3_file ); ?>" />
						 </td>

						 <td class="fes-url-choose-row" width="1%">
							<a href="#" class="edd-submit button upload_file_button" data-choose="<?php _e( 'Choose file', 'edd_fes' ); ?>" data-update="<?php _e( 'Insert file URL', 'edd_fes' ); ?>"><?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'edd_fes' ) ); ?></a>
						 </td>

						 <td>

						 </td>

						<?php if ( empty( $this->characteristics['single'] ) || $this->characteristics['single'] !== 'yes' ) { ?>
							 <td width="1%" class="fes-delete-row">
							 	<a href="#" class="edd-fes-delete delete"><?php _e( '&times;', 'edd_s3' ); ?></a>
							 </td>
						<?php } ?>
					</tr>
					<?php } ?>
					<tr class="add_new" style="display:none !important;" id="<?php echo sanitize_key( $this->name() ); ?>"></tr>
				</tbody>
				<?php if ( empty( $this->characteristics['count'] ) || $this->characteristics['count'] > 1 ) { ?>
				<tfoot>
					<tr>
						<th colspan="5">
							<a href="#" class="edd-submit button insert-file-row" id="<?php echo sanitize_key( $this->name() ); ?>"><?php _e( 'Add File', 'edd_fes' ); ?></a>
						</th>
					</tr>
				</tfoot>
				<?php } ?>
			</table>
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</div>';
		return $output;
	}

	/**
	 * Returns the HTML to render a field within the Form Builder.
	 *
	 * @access public
	 * @since  2.3
	 *
	 * @param  int    $index  Form builder index.
	 * @param  bool   $insert
	 * @return string         HTML to render field in Form Builder.
	 */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable       = $this->can_remove_from_formbuilder();
		$max_files_name  = sprintf( '%s[%d][count]', 'fes_input', $index );
		$max_files_value = $this->characteristics['count'];
		$count           = esc_attr( __( 'Number of files which can be uploaded.', 'edd_s3' ) );
		ob_start(); ?>
		<li class="custom-field custom_image">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
					<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name ); ?>
					<?php FES_Formbuilder_Templates::standard( $index, $this ); ?>

					<div class="fes-form-rows">
						<label><?php _e( 'Maximum number of files', 'edd_fes' ); ?></label>
						<input type="text" class="smallipopInput" name="<?php echo esc_html( $max_files_name ); ?>" value="<?php echo esc_html( $max_files_value ); ?>" title="<?php echo esc_html( $count ); ?>">
					</div>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	/**
	 * Validate the input data.
	 *
	 * @access public
	 * @since  2.3
	 *
	 * @param  array       $values       Input values.
	 * @param  int         $save_id      Save ID.
	 * @param  int         $user_id      User ID.
	 * @return bool|string $return_value False, or error otherwise.
	 */
	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();

		$return_value = false;

		if ( $this->required() ) {
			if ( ! empty( $values[ $name ] ) ) {
				if ( is_array( $values[ $name ] ) ) {

				} else {
					$return_value = __( 'Please fill out this field.', 'edd_s3' );
				}
			} else {
				$return_value = __( 'Please fill out this field.', 'edd_s3' );
			}
		}

		return apply_filters( 'fes_validate_' . $this->template() . '_field', $return_value, $values, $name, $save_id, $user_id );
	}

	/**
	 * Sanitize given input data.
	 *
	 * @access public
	 * @since  2.3
	 *
	 * @param  array $values       Input values.
	 * @param  int   $save_id      Save ID.
	 * @param  int   $user_id      User ID.
	 * @return array $return_value Sanitized input data.
	 */
	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( ! empty( $values[ $name ] ) ) {
			if ( is_array( $values[ $name ] ) ){
				foreach( $values[ $name ] as $key => $option  ){
					$values[ $name ][ $key ] = sanitize_text_field( trim( $values[ $name ][ $key ] ) );
				}
			}
		}
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}
}
