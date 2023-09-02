<?php
// Meta-Box Generator
// How to use: $meta_value = get_post_meta( $post_id, $field_id, true );
// Example: get_post_meta( get_the_ID(), "my_metabox_field", true );
class ShiparonAdvancedOptionsMetabox {

	/**
	 * List of screens to add the metaboxes to.
	 *
	 * @var array
	 */
	private $screens = array( 'shop_order' );

	/**
	 * List of data for each of the admin fields.
	 *
	 * @var array
	 */
	private $fields = array(
		array(
			'label'   => 'Nom',
			'id'      => 'nom',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'label'   => 'Gouvernerat',
			'id'      => 'gouvernerat',
			'type'    => 'select',
			'options' => array(
				'Ariana',
				'Béja',
				'Ben Arous',
				'Bizerte',
				'Gabès',
				'Gafsa',
				'Jendouba',
				'Kairouan',
				'Kasserine',
				'Kébili',
				'La Manouba',
				'Le Kef',
				'Mahdia',
				'Médenine',
				'Monastir',
				'Nabeul',
				'Sfax',
				'Sidi Bouzid',
				'Siliana',
				'Sousse',
				'Tataouine',
				'Tozeur',
				'Tunis',
				'Zaghouan',
			),
			'default' => array(),
		),
		array(
			'label'   => 'Ville',
			'id'      => 'ville',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'label'   => 'Addresse',
			'id'      => 'adresse',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'label'   => 'Tel',
			'id'      => 'tel',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'label'   => 'Tel 2',
			'id'      => 'tel2',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'label'   => 'Désignation',
			'id'      => 'designation',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'label'   => 'Article',
			'id'      => 'article',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'label'   => "Nombre d'articles",
			'id'      => 'nb_article',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'label'   => 'Prix',
			'id'      => 'prix',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'label'   => 'Message',
			'id'      => 'msg',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'label'   => 'Échange',
			'id'      => 'echange',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'label'   => 'Nbr Échange',
			'id'      => 'nb_echange',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'label'   => '',
			'value'   => 'Save & Submit',
			'id'      => 'submit_form_data',
			'type'    => 'button',
			'default' => '',
		),
		array(
			'label'   => 'Export',
			'value'   => '',
			'id'      => 'shiparon_export_btn',
			'type'    => 'href',
			'default' => '',
		),
	);

	/**
	 * Setup WordPress actions
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_fields' ) );
	}

	/**
	 * Add the metaboxes for the list of screens
	 */
	public function add_meta_boxes() {
		foreach ( $this->screens as $s ) {
			add_meta_box(
				'ShiparonAdvancedOptions',
				__( 'Shiparon Advanced Options', 'shiparon' ),
				array( $this, 'meta_box_callback' ),
				$s,
				'normal',
				'default'
			);
		}
	}

	/**
	 * Callback for rendering the metabox
	 *
	 * @param  WP_Post $post The post object rendering the metabox for
	 */
	public function meta_box_callback( $post ) {
		wp_nonce_field( 'AdvancedOptions_data', 'AdvancedOptions_nonce' );
		$this->field_generator( $post );
	}

	/**
	 * Generate fields.
	 *
	 * @param  [type] $post
	 */
	public function field_generator( $post ) {
		$output = '';
		$order  = new WC_Order( $post->ID );

		if ( $order ) {
			$export_url = get_post_meta( $post->ID, 'shiparon_order_export_url', true );
			foreach ( $this->fields as $field ) {
				$label      =
					'<label for="' .
					$field['id'] .
					'">' .
					$field['label'] .
					'</label>';
				$meta_value = get_post_meta( $post->ID, $field['id'], true );
				if ( empty( $meta_value ) ) {
					if ( $field['id'] === 'prix' ) {
						$field['default'] = $order->total;
					}
					if ( $field['id'] === 'nom' ) {
						$field['default'] = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
					}
					if ( $field['id'] === 'adresse' ) {
						$field['default'] = $order->get_billing_address_1();
					}
					if ( $field['id'] === 'tel' ) {
						$field['default'] = $order->get_billing_phone();
					}
					if ( $field['id'] === 'ville' ) {
						$field['default'] = $order->get_billing_city();
					}
					if ( $field['id'] === 'article' || $field['id'] === 'designation' ) {
						$field['default'] = get_option( 'shiparon_options' )['default_product_title'];
					}
					if ( $field['id'] === 'nb_article' ) {
						$field['default'] = $order->get_item_count();
					}
					if ( $field['id'] === 'href' ) {
						$field['value']   = $export_url;
						$field['default'] = $export_url;
					}
					if ( isset( $field['default'] ) ) {
						$meta_value = $field['default'];
					}
				}
				switch ( $field['type'] ) {
					case 'select':
						$input = sprintf(
							'<select id="%s" name="%s" %s>',
							$field['id'],
							$field['id'],
							isset( $export_url ) && ! empty( $export_url ) ? 'disabled' : '',
						);
						foreach ( $field['options'] as $key => $value ) {
							$field_value = ! is_numeric( $key ) ? $key : $value;
							$input      .= sprintf(
								'<option %s value="%s">%s</option>',
								$meta_value === $field_value ? 'selected' : '',
								$field_value,
								$value
							);
						}
						$input .= '</select>';
						break;

					case 'button':
						$label = '';
						$input = sprintf(
							'<button id="%s" type="%s" class="button button-primary calculate-action" %s>%s</button>',
							$field['id'],
							$field['type'],
							isset( $export_url ) && ! empty( $export_url ) ? 'disabled' : '',
							$field['value']
						);
						break;
					case 'href':
						$label = '';
						$input = sprintf(
							'<a id="%s" href="%s" target="_blank" class="button button-primary export-action" %s>%s</a>',
							$field['id'],
							$export_url,
							isset( $export_url ) && ! empty( $export_url ) ? '' : 'disabled',
							$field['label']
						);
						break;

					default:
						$input = sprintf(
							'<input %s id="%s" name="%s" type="%s" value="%s" %s>',
							$field['type'] !== 'color'
								? 'style="width: 100%"'
								: '',
							$field['id'],
							$field['id'],
							$field['type'],
							$meta_value,
							isset( $export_url ) && ! empty( $export_url ) ? 'disabled' : '',
						);
				}
				$output .= $this->format_rows( $label, $input, $field['type'] );
			}
			ob_start();
			echo '<table class="form-table"><tbody>' .
				$output .
				'</tbody></table>';
			?>
				<script>
					( function() {
						const $ = jQuery
						jQuery('#submit_form_data').click((e) => {
							e.preventDefault();
							$('#submit_form_data').attr('disabled', true);
							const form = $('#ShiparonAdvancedOptions').find('input:not([type="hidden"]), select, textarea');
							var formData = new FormData();
							const formInput = form.serializeArray()

							for (let key in formInput) {
								formData.append(formInput[key]['name'], formInput[key]['value'])
							}
							var baseUrl = `
							<?php
							echo get_option( 'shiparon_options' )['shiparon_base_url'];
							?>
							`;
							var settings = {
								'url': baseUrl,
								'method': "POST",
								'timeout': 0,
								'processData': false,
								'mimeType': "multipart/form-data",
								'contentType': false,
								'data': formData
							};

							if (baseUrl) {
								$.ajax(settings).done(function (response) {
									form.attr('disabled', false);
									$('#shiparon_export_btn').attr('disabled', false);
									$('#shiparon_export_btn').attr('href', JSON.parse(response)?.lien);
									document.cookie = `shiparon_order_export_url_${<?php echo $post->ID; ?>}=${JSON.parse(response)?.lien}`;
									<?php
										$export_url = $_COOKIE[ 'shiparon_order_export_url_' . $post->ID ];
									if ( ! add_post_meta( $post->ID, 'shiparon_order_export_url', $export_url, true ) ) {
										update_post_meta( $post->ID, 'shiparon_order_export_url', $export_url );
									}
									?>
								}).fail(function() {
									$('#submit_form_data').attr('disabled', false);
								});
							}
						})
					} )();
				</script>
				<?php
		}
	}

	/**
	 * Format rows.
	 *
	 * @param  [type] $label
	 * @param  [type] $input
	 * @param  [type] $field_type
	 */
	public function format_rows( $label, $input, $field_type ) {
		if ( $field_type !== 'button' || $field_type !== 'href' ) {
			return '<div style="margin-top: 10px;"><strong>' .
				$label .
				'</strong></div><div>' .
				$input .
			'</div>';
		}
		return $input;
	}

	/**
	 * Save the metabox values to the database
	 *
	 * @param  Integer $post_id The ID of the post savings fields for
	 */
	public function save_fields( $post_id ) {
		if ( ! isset( $_POST['AdvancedOptions_nonce'] ) ) {
			return $post_id;
		}
		$nonce = $_POST['AdvancedOptions_nonce'];
		if ( ! wp_verify_nonce( $nonce, 'AdvancedOptions_data' ) ) {
			return $post_id;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		foreach ( $this->fields as $field ) {
			if ( isset( $_POST[ $field['id'] ] ) ) {
				switch ( $field['type'] ) {
					case 'email':
						$_POST[ $field['id'] ] = sanitize_email(
							$_POST[ $field['id'] ]
						);
						break;
					case 'text':
						$_POST[ $field['id'] ] = sanitize_text_field(
							$_POST[ $field['id'] ]
						);
						break;
				}
				update_post_meta( $post_id, $field['id'], $_POST[ $field['id'] ] );
			} elseif ( $field['type'] === 'checkbox' ) {
				update_post_meta(
					$post_id,
					$field['shiparon_order_meta']['id'],
					'0'
				);
			}
		}
	}

	/**
	 * Returns an option value.
	 *
	 * @param  string $option_name The name of the option to get the value for.
	 */
	protected function get_option_value( $option_name ) {
		$option = get_option( $this->option_name );
		if ( ! array_key_exists( $option_name, $option ) ) {
			return array_key_exists( 'default', $this->settings[ $option_name ] )
				? $this->settings[ $option_name ]['default']
				: '';
		}
		return $option[ $option_name ];
	}
}

if ( class_exists( 'ShiparonAdvancedOptionsMetabox' ) ) {
	new ShiparonAdvancedOptionsMetabox();
}
