<?php

/**
 * Class Shiparon_Options_Panel
 *
 * @package Shiparon
 */
class Shiparon_Options_Panel {

   /**
     * Options panel arguments.
     */
    protected $args = [];

    /**
     * Options panel title.
     */
    protected $title = '';

    /**
     * Options panel slug.
     */
    protected $slug = '';

    /**
     * Option name to use for saving our options in the database.
     */
    protected $option_name = '';

    /**
     * Option group name.
     */
    protected $option_group_name = '';

    /**
     * User capability allowed to access the options page.
     */
    protected $user_capability = '';

    /**
     * Our array of settings.
     */
    protected $settings = [];

    /**
     * Class constructor.
     *
     * @param array $args     The arguments for the options panel.
     * @param array $settings The settings to register.
     */
    public function __construct( array $args, array $settings ) {
        $this->args              = $args;
        $this->settings          = $settings;
        $this->title             = $this->args['title'] ?? esc_html__( 'Options', 'text_domain' );
        $this->slug              = $this->args['slug'] ?? sanitize_key( $this->title );
        $this->option_name       = $this->args['option_name'] ?? sanitize_key( $this->title );
        $this->option_group_name = $this->option_name . '_group';
        $this->user_capability   = $args['user_capability'] ?? 'manage_options';

        add_action( 'admin_menu', [ $this, 'register_menu_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    /**
     * Register the new menu page.
     */
    public function register_menu_page() {
        add_menu_page(
            $this->title,
            $this->title,
            $this->user_capability,
            $this->slug,
            [ $this, 'render_options_page' ]
        );
    }

    /**
     * Register the settings.
     */
    public function register_settings() {
        register_setting( $this->option_group_name, $this->option_name, [
            'sanitize_callback' => [ $this, 'sanitize_fields' ],
            'default'           => $this->get_defaults(),
        ] );

        add_settings_section(
            $this->option_name . '_sections',
            false,
            false,
            $this->option_name
        );

        foreach ( $this->settings as $key => $args ) {
            $type = $args['type'] ?? 'text';
            $callback = "render_{$type}_field";
            if ( method_exists( $this, $callback ) ) {
                $tr_class = '';
                if ( array_key_exists( 'tab', $args ) ) {
                    $tr_class .= 'wpex-tab-item wpex-tab-item--' . sanitize_html_class( $args['tab'] );
                }
                add_settings_field(
                    $key,
                    $args['label'],
                    [ $this, $callback ],
                    $this->option_name,
                    $this->option_name . '_sections',
                    [
                        'label_for' => $key,
                        'class'     => $tr_class
                    ]
                );
            }
        }
    }

    /**
     * Saves our fields.
     */
    public function sanitize_fields( $value ) {
        $value = (array) $value;
        $new_value = [];
        foreach ( $this->settings as $key => $args ) {
            $field_type = $args['type'];
            $new_option_value = $value[$key] ?? '';
            if ( $new_option_value ) {
                $sanitize_callback = $args['sanitize_callback'] ?? $this->get_sanitize_callback_by_type( $field_type );
                $new_value[$key] = call_user_func( $sanitize_callback, $new_option_value, $args );
            } elseif ( 'checkbox' === $field_type ) {
                $new_value[$key] = 0;
            }
        }
        return $new_value;
    }

    /**
     * Returns sanitize callback based on field type.
     */
    protected function get_sanitize_callback_by_type( $field_type ) {
        switch ( $field_type ) {
            case 'select':
                return [ $this, 'sanitize_select_field' ];
                break;
            case 'textarea':
                return 'wp_kses_post';
                break;
            case 'checkbox':
                return [ $this, 'sanitize_checkbox_field' ];
                break;
            default:
            case 'text':
                return 'sanitize_text_field';
                break;
        }
    }

    /**
     * Returns default values.
     */
    protected function get_defaults() {
        $defaults = [];
        foreach ( $this->settings as $key => $args ) {
            $defaults[$key] = $args['default'] ?? '';
        }
        return $defaults;
    }

    /**
     * Sanitizes the checkbox field.
     */
    protected function sanitize_checkbox_field( $value = '', $field_args = [] ) {
        return ( 'on' === $value ) ? 1 : 0;
    }

     /**
     * Sanitizes the select field.
     */
    protected function sanitize_select_field( $value = '', $field_args = [] ) {
        $choices = $field_args['choices'] ?? [];
        if ( array_key_exists( $value, $choices ) ) {
            return $value;
        }
    }

    /**
     * Renders the options page.
     */
    public function render_options_page() {
        if ( ! current_user_can( $this->user_capability ) ) {
            return;
        }

        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error(
               $this->option_name . '_mesages',
               $this->option_name . '_message',
               esc_html__( 'Settings Saved', 'navex' ),
               'updated'
            );
        }

        settings_errors( $this->option_name . '_mesages' );

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <?php $this->render_tabs(); ?>
            <form action="options.php" method="post" class="wpex-options-form">
                <?php
                    settings_fields( $this->option_group_name );
                    do_settings_sections( $this->option_name );
                    submit_button( 'Save Settings' );
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Renders options page tabs.
     */
    protected function render_tabs() {
        if ( empty( $this->args['tabs'] ) ) {
            return;
        }

        $tabs = $this->args['tabs'];
        ?>
        <h2 class="nav-tab-wrapper wpex-tabs"><?php
            $first_tab = true;
            foreach ( $tabs as $id => $label ) {?>
                <a href="#" data-tab="<?php echo esc_attr( $id ); ?>" class="nav-tab<?php echo ( $first_tab ) ? ' nav-tab-active' : ''; ?>"><?php echo ucfirst( $label ); ?></a>
                <?php
                $first_tab = false;
            }
        ?></h2>

        <script>
            ( function() {
                document.addEventListener( 'click', ( event ) => {
                    const target = event.target;
                    if ( ! target.closest( '.wpex-tabs a' ) ) {
                        return;
                    }
                    event.preventDefault();
                    document.querySelectorAll( '.wpex-tabs a' ).forEach( ( tablink ) => {
                        tablink.classList.remove( 'nav-tab-active' );
                    } );
                    target.classList.add( 'nav-tab-active' );
                    targetTab = target.getAttribute( 'data-tab' );
                    document.querySelectorAll( '.wpex-options-form .wpex-tab-item' ).forEach( ( item ) => {
                        if ( item.classList.contains( `wpex-tab-item--${targetTab}` ) ) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    } );
                } );
                document.addEventListener( 'DOMContentLoaded', function () {
                    document.querySelector( '.wpex-tabs .nav-tab' ).click();
                }, false );
            } )();
        </script>

        <?php
    }

    /**
     * Returns an option value.
     */
    protected function get_option_value( $option_name ) {
        $option = get_option( $this->option_name );
        if ( ! array_key_exists( $option_name, $option ) ) {
            return array_key_exists( 'default', $this->settings[$option_name] ) ? $this->settings[$option_name]['default'] : '';
        }
        return $option[$option_name];
    }

    /**
     * Renders a text field.
     */
    public function render_text_field( $args ) {
        $option_name = $args['label_for'];
        $value       = $this->get_option_value( $option_name );
        $description = $this->settings[$option_name]['description'] ?? '';
        ?>
            <input
                type="text"
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                name="<?php echo $this->option_name; ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
                value="<?php echo esc_attr( $value ); ?>">
            <?php if ( $description ) { ?>
                <p class="description"><?php echo esc_html( $description ); ?></p>
            <?php } ?>
        <?php
    }

    /**
     * Renders a textarea field.
     */
    public function render_textarea_field( $args ) {
        $option_name = $args['label_for'];
        $value       = $this->get_option_value( $option_name );
        $description = $this->settings[$option_name]['description'] ?? '';
        $rows        = $this->settings[$option_name]['rows'] ?? '4';
        $cols        = $this->settings[$option_name]['cols'] ?? '50';
        ?>
            <textarea
                type="text"
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                rows="<?php echo esc_attr( absint( $rows ) ); ?>"
                cols="<?php echo esc_attr( absint( $cols ) ); ?>"
                name="<?php echo $this->option_name; ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo esc_attr( $value ); ?></textarea>
            <?php if ( $description ) { ?>
                <p class="description"><?php echo esc_html( $description ); ?></p>
            <?php } ?>
        <?php
    }

    /**
     * Renders a checkbox field.
     */
    public function render_checkbox_field( $args ) {
        $option_name = $args['label_for'];
        $value       = $this->get_option_value( $option_name );
        $description = $this->settings[$option_name]['description'] ?? '';
        ?>
            <input
                type="checkbox"
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                name="<?php echo $this->option_name; ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
                <?php checked( $value, 1, true ); ?>
            >
            <?php if ( $description ) { ?>
                <p class="description"><?php echo esc_html( $description ); ?></p>
            <?php } ?>
        <?php
    }

    /**
     * Renders a select field.
     */
    public function render_select_field( $args ) {
        $option_name = $args['label_for'];
        $value       = $this->get_option_value( $option_name );
        $description = $this->settings[$option_name]['description'] ?? '';
        $choices     = $this->settings[$option_name]['choices'] ?? [];
        ?>
            <select
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                name="<?php echo $this->option_name; ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
            >
                <?php foreach ( $choices as $choice_v => $label ) { ?>
                    <option value="<?php echo esc_attr( $choice_v ); ?>" <?php selected( $choice_v, $value, true ); ?>><?php echo esc_html( $label ); ?></option>
                <?php } ?>
            </select>
            <?php if ( $description ) { ?>
                <p class="description"><?php echo esc_html( $description ); ?></p>
            <?php } ?>
        <?php
    }

}

$panel_args = [
    'title'           => 'Navex Options',
    'option_name'     => 'navex_options',
    'slug'            => 'navex-options-panel',
    'user_capability' => 'manage_options',
];

$panel_settings = [
    'navex_base_url' => [
        'label'       => esc_html__( 'API endpoint', 'navex' ),
        'type'        => 'text'
    ],
    'default_product_title' => [
        'label'       => esc_html__( 'Default Product title', 'navex' ),
        'type'        => 'text'
    ],
];

if (class_exists('NAVEX_Options_Panel')) {
    new NAVEX_Options_Panel( $panel_args, $panel_settings );
};
