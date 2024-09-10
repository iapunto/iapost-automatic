<?php
/**
 * Meta API Connection Class for IAPost Automatic
 *
 * Handles connection and authentication with Facebook and Instagram APIs.
 *
 * @package IAPost Automatic
 * @since 1.0.0
 */

// Asegúrate de que el SDK de Facebook para PHP esté instalado y disponible

use Facebook\Facebook;

class MetaApiConnection {

    private $facebook; // Instancia del SDK de Facebook
    private $app_id;
    private $app_secret;
    private $redirect_uri;
    private $access_token; // Token de acceso (almacenado en la base de datos)

    /**
     * Constructor
     */
    public function __construct() {
        // Carga la configuración de la API desde la base de datos
        $this->load_api_config();

        // Verificar si la configuración de Facebook está disponible
        if ( ! empty( $this->app_id ) && ! empty( $this->app_secret ) ) {
            // Inicializa el SDK de Facebook solo si la configuración está disponible
            $this->facebook = new Facebook([
                'app_id' => $this->app_id,
                'app_secret' => $this->app_secret,
                'default_graph_version' => 'v17.0', 
            ]);
        }
    }

    /**
     * Carga la configuración de la API desde la base de datos
     */
    private function load_api_config() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iapost_api_settings';
    
        // Consulta para obtener la configuración de Facebook
        $config = $wpdb->get_row( 
            $wpdb->prepare( "SELECT * FROM $table_name WHERE social_network = %s", 'facebook' ) 
        );
    
        // Manejo de errores en caso de que la consulta falle
        if ( $wpdb->last_error ) {
            error_log( 'Error al obtener la configuración de Facebook de la base de datos: ' . $wpdb->last_error );
            return; // Salir de la función si hay un error en la consulta
        }
    
        // Mensaje de depuración para verificar si se encontró la configuración
        if ( $config ) {
            error_log( 'Configuración de Facebook encontrada en la base de datos.' );
            $this->app_id = $config->app_id;
            $this->app_secret = $config->app_secret;
            $this->redirect_uri = $config->redirect_uri;
    
            // Verificar si existe el access token del usuario actual
            $this->access_token = get_user_meta( get_current_user_id(), 'facebook_access_token', true );
            if ( $this->access_token ) {
                error_log( 'Access token de Facebook encontrado para el usuario actual.' );
            } else {
                error_log( 'No se encontró access token de Facebook para el usuario actual.' );
            }
        } else {
            error_log( 'No se encontró configuración de Facebook en la base de datos.' );
        }
    }

    /**
     * Verifica si la configuración de Facebook está disponible
     *
     * @return bool True si la configuración está disponible, false en caso contrario
     */
    public function is_facebook_configured() {
        return ! empty( $this->app_id ) && ! empty( $this->app_secret );
    }

    /**
     * Obtiene la URL de autorización de Facebook
     *
     * @param array $permissions Permisos solicitados (opcional)
     * @return string La URL de autorización
     */
    public function get_authorization_url( $permissions = ['email', 'public_profile', 'pages_manage_posts'] ) {
        $helper = $this->facebook->getRedirectLoginHelper();
        return $helper->getLoginUrl( $this->redirect_uri, $permissions );
    }

    /**
     * Maneja la respuesta de autorización de Facebook y almacena el token de acceso
     *
     * @param string $code Código de autorización recibido de Facebook
     * @return bool True si la autorización fue exitosa, false en caso contrario
     */
    public function handle_authorization_response( $code ) {
        $helper = $this->facebook->getRedirectLoginHelper();

        try {
            $access_token = $helper->getAccessToken( $this->redirect_uri, $code );
            
            // Almacena el token de acceso en la base de datos (metadatos del usuario)
            update_user_meta( get_current_user_id(), 'facebook_access_token', $access_token->getValue() );

            return true;
        } catch( Facebook\Exceptions\FacebookResponseException $e ) {
            // Manejo de errores de la API de Facebook
            error_log( 'Error de la API de Facebook: ' . $e->getMessage() );
            return false;
        } catch( Facebook\Exceptions\FacebookSDKException $e ) {
            // Manejo de errores del SDK de Facebook
            error_log( 'Error del SDK de Facebook: ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Publica un post en Facebook o Instagram
     *
     * @param string $social_network 'facebook' o 'instagram'
     * @param array $post_data Datos del post (mensaje, imagen, etc.)
     * @return bool True si la publicación fue exitosa, false en caso contrario
     */
    public function publish_post( $social_network, $post_data ) {
        if ( ! $this->access_token ) {
            return false; // No hay token de acceso válido
        }

        try {
            // Configura el token de acceso en el SDK de Facebook
            $this->facebook->setDefaultAccessToken( $this->access_token );

            // Prepara los datos del post según la red social
            if ( $social_network === 'facebook' ) {
                $endpoint = '/me/feed'; // Publicar en el muro del usuario
                $params = [
                    'message' => $post_data['message'],
                    // ... otros parámetros según sea necesario
                ];
            } else if ( $social_network === 'instagram' ) {
                $endpoint = '/me/media'; // Publicar en Instagram (requiere permisos adicionales)
                $params = [
                    'image_url' => $post_data['image_url'],
                    'caption' => $post_data['caption'],
                    // ... otros parámetros según sea necesario
                ];
            } else {
                return false; // Red social no válida
            }

            // Realiza la solicitud a la API de Facebook
            $response = $this->facebook->post( $endpoint, $params );

            // Verifica la respuesta y devuelve el resultado
            return $response->getDecodedBody(); 
        } catch( Facebook\Exceptions\FacebookResponseException $e ) {
            // Manejo de errores de la API de Facebook
            error_log( 'Error de la API de Facebook: ' . $e->getMessage() );
            return false;
        } catch( Facebook\Exceptions\FacebookSDKException $e ) {
            // Manejo de errores del SDK de Facebook
            error_log( 'Error del SDK de Facebook: ' . $e->getMessage() );
            return false;
        }
    }

    // ... (Otros métodos para interactuar con las APIs de Meta)
}