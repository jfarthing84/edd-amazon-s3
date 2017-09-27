<?php
/**
 * Frontend Submissions S3 Field.
 *
 * @package  EDD_Amazon_S3
 * @category Integrations
 * @author   Easy Digital Downloads
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDD_Amazon_S3_FES_Field Class.
 *
 * @since 2.3
 *
 * @see   FES_Field
 */
class EDD_Amazon_S3_FES_Field extends FES_Field {

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
			'registration'   => false,
			'submission'     => true,
			'vendor-contact' => false,
			'profile'        => false,
			'login'          => false,
		),
		'position'    => 'extension',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => false,
			'can_add_to_formbuilder'      => true,
		),
		'template'    => 'edd_s3',
		'title'       => 'Amazon S3 Upload',
		'phoenix'     => false,
	);

	/**
	 * Characteristics are things that can change from field to field of the same field type. Like the placeholder
	 * between two email fields. Stored in db.
	 *
	 * @access public
	 * @since  2.3
	 * @var    array
	 */
	public $characteristics = array(
		'name'        => 'edd_s3',
		'template'    => 'edd_s3',
		'public'      => true,
		'required'    => false,
		'label'       => 'Amazon S3 Upload',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
		'extension'   => array(),
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
	 * Returns the HTML to render a field in admin.
	 *
	 * @access public
	 * @since  2.3
	 *
	 * @param int $user_id  Save ID.
	 * @param int $readonly Is the field read only?
	 *
	 * @return string HTML to render field in admin.
	 */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		/**
		 * Filter the User ID.
		 *
		 * @param int $user_id User ID.
		 * @param int $id      Field ID.
		 */
		$user_id = apply_filters( 'fes_render_edd_s3_field_user_id_admin', $user_id, $this->id );

		/**
		 * Filter the `readonly` value.
		 *
		 * @param bool $readonly Whether the field is readonly or not.
		 * @param int  $user_id  User ID.
		 * @param int  $id       Field ID.
		 */
		$readonly = apply_filters( 'fes_render_edd_s3_field_readonly_admin', $readonly, $user_id, $this->id );

		$value = $this->get_field_value_admin( $this->save_id, $user_id, $readonly );

		if ( ! is_array( $value ) || empty( $value ) ) {
			$value = array( 0 => '' );
		}

		$output = '';
		$output .= sprintf( '<div class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output .= $this->label( $readonly );

		ob_start(); ?>
		<div class="fes-fields">
			<table class="multiple <?php echo sanitize_key( $this->name() ); ?>">
				<thead>
				<tr>
					<th width="60%" class="fes-file-column"><?php _e( 'File URL', 'edd_s3' ); ?></th>
					<th width="33%" class="fes-download-file"><?php _e( 'Download File', 'edd_s3' ); ?></th>
					<th width="1%" class="fes-remove-column">&nbsp;</th>
				</tr>
				</thead>
				<tbody class="fes-variations-list-<?php echo sanitize_key( $this->name() ); ?>">
				<?php foreach ( $value as $key => $url ) { ?>
					<tr class="fes-single-variation">
						<td width="60%" class="fes-url-row">
							<input type="text" class="fes-file-value" data-formid="<?php echo $this->form; ?>" data-fieldname="<?php echo $this->name(); ?>" placeholder="<?php _e( 'http://', 'edd_s3' ); ?>" name="<?php echo $this->name(); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo isset( $url['file'] ) ? esc_attr( $url['file'] ) : ''; ?>" />
						</td>
						<td width="33%" class="fes-url-row">
							<a href="#" class="edd-submit button upload_file_button" data-choose="<?php _e( 'Choose file', 'edd_s3' ); ?>" data-update="<?php _e( 'Insert file URL', 'edd_s3' ); ?>"><?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'edd_s3' ) ); ?></a>
							<?php if ( isset( $url['file'] ) && ! empty( $url['file'] ) ) { ?>
								<a href="<?php echo edd_amazon_s3()->get_s3_url( esc_attr( $url['file'] ) ); ?>"><?php _e( 'Download File', 'edd_s3' ); ?></a>
							<?php } ?>
						</td>
						<td width="1%" class="fes-delete-row">
							<a href="#" class="edd-fes-delete delete"><?php _e( '&times;', 'edd_s3' ); ?></a>
						</td>
					</tr>
				<?php } ?>
				<tr class="add_new" style="display:none !important;" id="<?php echo sanitize_key( $this->name() ); ?>"></tr>
				</tbody>
				<?php if ( empty( $this->characteristics['count'] ) || $this->characteristics['count'] > 1 ) { ?>
					<tfoot>
					<tr>
						<th colspan="5">
							<a href="#" class="edd-submit button insert-file-row" id="<?php echo sanitize_key( $this->name() ); ?>"><?php _e( 'Add File', 'edd_s3' ); ?></a>
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
	 * Returns the HTML to render a field in frontend.
	 *
	 * @access public
	 * @since  2.3
	 *
	 * @param int $user_id  Save ID.
	 * @param int $readonly Is the field read only?
	 *
	 * @return string HTML to render field in admin.
	 */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		/**
		 * Filter the User ID.
		 *
		 * @param int $user_id User ID.
		 * @param int $id      Field ID.
		 */
		$user_id = apply_filters( 'fes_render_edd_s3_field_user_id_frontend', $user_id, $this->id );

		/**
		 * Filter the `readonly` value.
		 *
		 * @param bool $readonly Whether the field is readonly or not.
		 * @param int  $user_id  User ID.
		 * @param int  $id       Field ID.
		 */
		$readonly = apply_filters( 'fes_render_edd_s3_field_readonly_frontend', $readonly, $user_id, $this->id );

		$value = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );

		if ( ! is_array( $value ) || empty( $value ) ) {
			$value = array( 0 => '' );
		}

		$output = '';
		$output .= sprintf( '<div class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output .= $this->label( $readonly );

		ob_start(); ?>
		<div class="fes-fields">
			<table class="multiple <?php echo sanitize_key( $this->name() ); ?>">
				<thead>
				<tr>
					<th width="60%" class="fes-file-column"><?php _e( 'File URL', 'edd_s3' ); ?></th>
					<?php if ( fes_is_admin() ) { ?>
						<th width="33%" class="fes-download-file">
							<?php _e( 'Download File', 'edd_s3' ); ?>
						</th>
					<?php } ?>
					<th width="1%" class="fes-remove-column">&nbsp;</th>
				</tr>
				</thead>
				<tbody class="fes-variations-list-<?php echo sanitize_key( $this->name() ); ?>">
				<?php foreach ( $value as $key => $url ) { ?>
					<tr class="fes-single-variation">
						<td width="60%" class="fes-url-row">
							<input type="text" class="fes-file-value" data-formid="<?php echo $this->form; ?>" data-fieldname="<?php echo $this->name(); ?>" placeholder="<?php _e( 'http://', 'edd_s3' ); ?>" name="<?php echo $this->name(); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo isset( $url['file'] ) ? esc_attr( $url['file'] ) : ''; ?>" />
						</td>
						<td width="33%" class="fes-url-row">
							<a href="#" class="edd-submit button upload_file_button" data-choose="<?php _e( 'Choose file', 'edd_s3' ); ?>" data-update="<?php _e( 'Insert file URL', 'edd_s3' ); ?>"><?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'edd_s3' ) ); ?></a>
						</td>
						<td width="1%" class="fes-delete-row">
							<a href="#" class="edd-fes-delete delete"><?php _e( '&times;', 'edd_s3' ); ?></a>
						</td>
					</tr>
				<?php } ?>
				<tr class="add_new" style="display:none !important;" id="<?php echo sanitize_key( $this->name() ); ?>"></tr>
				</tbody>
				<?php if ( empty( $this->characteristics['count'] ) || $this->characteristics['count'] > 1 ) { ?>
					<tfoot>
					<tr>
						<th colspan="5">
							<a href="#" class="edd-submit button insert-file-row" id="<?php echo sanitize_key( $this->name() ); ?>"><?php _e( 'Add File', 'edd_s3' ); ?></a>
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
	 * @param int  $index  Form builder index.
	 * @param bool $insert Whether the field is being inserted.
	 *
	 * @return string HTML to render field in Form Builder.
	 */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder();

		ob_start(); ?>
		<li class="custom-field edd_s3_upload">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name ); ?>
				<?php FES_Formbuilder_Templates::standard( $index, $this ); ?>
				<div class="fes-form-rows">
					<label><?php _e( 'Allowed Files', 'edd_fes' ); ?></label>

					<div class="fes-form-sub-fields">
						<?php
						$args = array(
							'options'          => fes_allowed_extensions(),
							'name'             => sprintf( '%s[%d][extension][]', 'fes_input', $index ),
							'class'            => 'select long',
							'id'               => sprintf( '%s_%d_extension', 'fes_input', $index ),
							'selected'         => isset( $this->characteristics['extension'] ) ? $this->characteristics['extension'] : array(),
							'chosen'           => true,
							'placeholder'      => esc_attr( __( 'Pick which file types to allow. Leave empty for all types.', 'edd_s3' ) ),
							'multiple'         => true,
							'show_option_all'  => false,
							'show_option_none' => false,
							'data'             => array( 'search-type'        => 'no_ajax',
							                             'search-placeholder' => __( 'Type to search all file types', 'edd_s3' ),
							),
						);
						echo EDD()->html->select( $args );
						?>
					</div>
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
	 * @param array $values  Input values.
	 *                       Default empty array.
	 * @param int   $save_id Save ID.
	 *                       Default -2.
	 * @param int   $user_id User ID.
	 *                       Default -2.
	 *
	 * @return mixed|false Error message, otherwise false.
	 */
	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();

		$return_value = false;

		if ( $this->required() ) {
			if ( ! empty( $values[ $name ] ) ) {
				if ( is_array( $values[ $name ] ) ) {
					foreach ( $values[ $name ] as $key => $file ) {
						/**
						 * We ensure that we can pass the FILTER_VALIDATE_URL validation at this stage as the file gets
						 * uploaded to Amazon S3 on save.
						 */
						if ( false === filter_var( $file, FILTER_VALIDATE_URL ) ) {
							$return_value = __( 'Please enter a valid URL.', 'edd_s3' );
							break;
						} else {
							if ( ! edd_is_local_file( $file ) ) {
								$return_value = __( 'Files must be uploaded through the upload form.', 'edd_s3' );
							}
						}
					}
				} else {
					$return_value = __( 'Please fill out this field.', 'edd_s3' );
				}
			} else {
				$return_value = __( 'Please fill out this field.', 'edd_s3' );
			}
		}

		if ( ! $return_value ) {
			if ( !empty( $values[ $name ] ) ) {
				if ( is_array( $values[ $name ] ) ){
					foreach( $values[ $name ] as $key => $file  ){
						if ( filter_var( $file, FILTER_VALIDATE_URL ) !== false && ! empty( $this->characteristics['extension'] ) ){
							$parts = parse_url( $file );
							$file_type = wp_check_filetype( basename( $parts["path"] ) );
							$file_type = $file_type['ext'];
							if ( ! in_array( $file_type, $this->characteristics['extension'] ) ) {
								$allowed_types = implode( ', ', array_values( $this->characteristics['extension'] ) );
								$return_value = sprintf( __( 'Please use files with one of these extensions: %s', 'edd_s3' ), $allowed_types );
								break;
							}
							if ( ! edd_is_local_file( $file ) ) {
								$return_value = __( 'Files must be uploaded through the upload form', 'edd_s3' );
								break;
							}
						}
					}
				}
			}
		}

		/**
		 * Filters the return values.
		 *
		 * @param array  $return_values Validated return values.
		 * @param array  $values        Pre-validated return values.
		 * @param string $name          Field name.
		 * @param int    $save_id       Save ID.
		 * @param int    $user_id       User ID.
		 */
		return apply_filters( 'fes_validate_' . $this->template() . '_field', $return_value, $values, $name, $save_id, $user_id );
	}

	/**
	 * Sanitize given input data.
	 *
	 * @access public
	 * @since  2.3
	 *
	 * @param  array $values  Input values.
	 * @param  int   $save_id Save ID.
	 * @param  int   $user_id User ID.
	 *
	 * @return array $return_value Sanitized input data.
	 */
	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();

		if ( ! empty( $values[ $name ] ) ) {
			if ( is_array( $values[ $name ] ) ) {
				foreach ( $values[ $name ] as $key => $option ) {
					$values[ $name ][ $key ] = sanitize_text_field( trim( $values[ $name ][ $key ] ) );
				}
			}
		}

		/**
		 * Filters the sanitized values.
		 *
		 * @param array  $values  Pre-validated return values.
		 * @param string $name    Field name.
		 * @param int    $save_id Save ID.
		 * @param int    $user_id User ID.
		 */
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}

	/**
	 * Save the field from the frontend; here we intercept the files uploaded from the
	 *  frontend and upload to Amazon S3.
	 *
	 * @access public
	 * @since  2.3
	 *
	 * @param array $value   Pre-validated return values.
	 * @param int   $save_id Save ID.
	 * @param int   $user_id User ID.
	 */
	public function save_field_frontend( $save_id = -2, $value = array(), $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $save_id == -2 ) {
			$save_id = $this->save_id;
		}

		/**
		 * Filter the User ID.
		 *
		 * @param int   $user_id User ID.
		 * @param int   $save_id Save ID.
		 * @param array $value   Input values.
		 */
		$user_id = apply_filters( 'fes_save_field_user_id_frontend', $user_id, $save_id, $value );

		/**
		 * Filter the save ID.
		 *
		 * @param array $value   Input values.
		 * @param int   $user_id User ID.
		 * @param int   $id      Field ID.
		 */
		$value = apply_filters( 'fes_save_field_value_frontend', $value, $save_id, $user_id );

		/**
		 * Run before any save actions occur.
		 *
		 * @since 2.3
		 *
		 * @param int   $save_id Save ID.
		 * @param array $value   Pre-defined return values.
		 * @param int   $user_id User ID.
		 */
		do_action( 'fes_save_field_before_save_frontend', $save_id, $value, $user_id );

		if ( ! is_array( $value ) ) {
			return;
		}

		if ( 'post' === $this->type ) {
			$files = array();

			if ( is_array( $value ) && ! empty( $value ) ) {
				foreach ( $value as $key => $url ) {
					$files[ $key ] = array(
						'name' => basename( $url ),
						'file' => $url,
					);
				}
			}

			if ( ! empty( $files ) && is_array( $files ) ) {
				foreach ( $files as $key => $file ) {
					$attachment_id = fes_get_attachment_id_from_url( $file['file'], get_current_user_id() );

					if ( ! $attachment_id ) {
						continue;
					}

					$user               = get_userdata( get_current_user_id() );
					$folder_name_option = edd_get_option( 'edd_amazon_s3_fes_folder_name', 'user_nicename' );
					$folder             = ( 'user_nicename' == $folder_name_option ) ? trailingslashit( $user->user_nicename ) : trailingslashit( $user->ID );

					$args = array(
						'file' => get_attached_file( $attachment_id, false ),
						'name' => $folder . basename( $file['name'] ),
						'type' => get_post_mime_type( $attachment_id ),
					);

					edd_amazon_s3()->upload_file( $args );

					$files[ $key ]['file'] = edd_get_option( 'edd_amazon_s3_bucket' ) . '/' . $folder . basename( $file['file'] );

					wp_delete_attachment( $attachment_id, true );
				}
			}

			$value       = update_post_meta( $save_id, $this->id, $files );
			$this->value = $value;
			do_action( 'fes_save_field_after_save_frontend', $this, $save_id, $value, $user_id );
		}

		/**
		 * Run after all save actions occur.
		 *
		 * @since 2.3
		 *
		 * @param int   $save_id Save ID.
		 * @param array $value   Pre-defined return values.
		 * @param int   $user_id User ID.
		 */
		do_action( 'fes_save_field_after_save_frontend', $save_id, $value, $user_id );
	}
}