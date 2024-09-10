<?php
/**
 * Admin Settings Class for IAPost Automatic
 *
 * Handles the plugin's settings page and form submission.
 *
 * @package IAPost Automatic
 * @since 1.0.0
 */

class AdminSettings {

    private $meta_api; // Instancia de MetaApiConnection

    /**
     * Constructor
     *
     * @param MetaApiConnection $meta_api Instance of the MetaApiConnection class
     */
    public function __construct( MetaApiConnection $meta_api ) {
        $this->meta_api = $meta_api;
    }

    /**
     * Renderiza la página de configuración
     */
    public function render_settings_page() {
        // Verificar si el usuario tiene permisos suficientes
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'No tienes suficientes permisos para acceder a esta página.', 'iapost-automatic' ) );
        }

        // Mostrar la página de configuración
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'iapost_automatic_settings_group' );
                do_settings_sections( 'iapost-automatic' );
                submit_button();
                ?>
            </form>

            <?php if ( ! $this->meta_api->is_facebook_configured() ) : ?>
                <div class="notice notice-warning is-dismissible">
                    <p><?php _e( '¡Por favor, configura tus credenciales de la API de Facebook antes de continuar!', 'iapost-automatic' ); ?></p>
                </div>
            <?php else : ?>
                <a href="<?php echo $this->meta_api->get_authorization_url(); ?>" class="button button-primary">
                    <?php _e( 'Conectar con Facebook', 'iapost-automatic' ); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Registra la configuración del plugin
     */
    public function register_settings() {
        register_setting( 'iapost_automatic_settings_group', 'iapost_automatic_settings' );

        // Sección de configuración de Facebook
        add_settings_section(
            'iapost_automatic_facebook_settings_section', 
            __( 'Configuración de Facebook', 'iapost-automatic' ), 
            array( $this, 'facebook_settings_section_callback' ), 
            'iapost-automatic'
        );

        // Campos de configuración de Facebook
        add_settings_field(
            'facebook_app_id', 
            __( 'Facebook App ID', 'iapost-automatic' ), 
            array( $this, 'facebook_app_id_callback' ), 
            'iapost-automatic', 
            'iapost_automatic_facebook_settings_section'
        );

        add_settings_field(
            'facebook_app_secret', 
            __( 'Facebook App Secret', 'iapost-automatic' ), 
            array( $this, 'facebook_app_secret_callback' ), 
            'iapost-automatic', 
            'iapost_automatic_facebook_settings_section'
        );

        add_settings_field(
            'facebook_redirect_uri', 
            __( 'Facebook Redirect URI', 'iapost-automatic' ), 
            array( $this, 'facebook_redirect_uri_callback' ), 
            'iapost-automatic', 
            'iapost_automatic_facebook_settings_section'
        );
    }

    /**
     * Callback para la sección de configuración de Facebook
     */
    public function facebook_settings_section_callback() {
        echo '<p>' . __( 'Ingresa las credenciales de tu aplicación de Facebook.', 'iapost-automatic' ) . '</p>';
    }

    /**
     * Callback para el campo Facebook App ID
     */
    public function facebook_app_id_callback() {
        $options = get_option( 'iapost_automatic_settings' );
        $facebook_app_id = isset( $options['facebook_app_id'] ) ? esc_attr( $options['facebook_app_id'] ) : '';
        echo '<input type="text" id="facebook_app_id" name="iapost_automatic_settings[facebook_app_id]" value="' . $facebook_app_id . '" />';
    }

    /**
     * Callback para el campo Facebook App Secret
     */
    public function facebook_app_secret_callback() {
        $options = get_option( 'iapost_automatic_settings' );
        $facebook_app_secret = isset( $options['facebook_app_secret'] ) ? esc_attr( $options['facebook_app_secret'] ) : '';
        echo '<input type="text" id="facebook_app_secret" name="iapost_automatic_settings[facebook_app_secret]" value="' . $facebook_app_secret . '" />';
    }

    /**
     * Callback para el campo Facebook Redirect URI
     */
    public function facebook_redirect_uri_callback() {
        $options = get_option( 'iapost_automatic_settings' );
        $facebook_redirect_uri = isset( $options['facebook_redirect_uri'] ) ? esc_attr( $options['facebook_redirect_uri'] ) : '';
        echo '<input type="text" id="facebook_redirect_uri" name="iapost_automatic_settings[facebook_redirect_uri]" value="' . $facebook_redirect_uri . '" />';
    }
}