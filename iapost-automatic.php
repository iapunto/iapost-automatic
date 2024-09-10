<?php
/**
 * Plugin Name:       IAPost Automatic
 * Plugin URI:        https://www.iapunto.com/iapost-automatic 
 * Description:       Automatiza la publicación de contenido en redes sociales desde tu sitio WordPress. 
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            IA Punto Agencia de Marketing Digital
 * Author URI:        https://www.iapunto.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       iapost-automatic
 * Domain Path:       /languages
 */

 require_once 'vendor/autoload.php'; // Ajusta la ruta si es necesario

 // Incluir el archivo de la clase MetaApiConnection
 require_once plugin_dir_path( __FILE__ ) . 'includes/class-meta-api-connection.php';

 // Incluir los archivos de las clases de administración
require_once plugin_dir_path( __FILE__ ) . 'admin/class-admin-calendar.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-admin-settings.php';

 // Declarar la variable global
global $iapost_automatic_meta_api;
global $iapost_automatic_admin_settings;
global $iapost_automatic_admin_calendar;

// Plugin initialization function
function iapost_automatic_init() {
  // Verificar y crear la tabla de configuraciones si no existe
  iapost_automatic_create_settings_table();
  // Register activation hook (optional)
  register_activation_hook( __FILE__, 'iapost_automatic_activate' );

  // Register deactivation hook (optional)
  register_deactivation_hook( __FILE__, 'iapost_automatic_deactivate' );

  // Load plugin text domain
  load_plugin_textdomain( 'iapost-automatic', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

  // Crear una instancia de la clase MetaApiConnection y asignarla a la variable global
  global $iapost_automatic_meta_api;
  $iapost_automatic_meta_api = new MetaApiConnection();
  
  global $iapost_automatic_admin_settings;
  $iapost_automatic_admin_settings = new AdminSettings( $iapost_automatic_meta_api );

  global $iapost_automatic_admin_calendar;
  $iapost_automatic_admin_calendar = new AdminCalendar();

  // Registrar la configuración del plugin (utilizando la instancia de AdminSettings)
  add_action( 'admin_init', array( $iapost_automatic_admin_settings, 'register_settings' ) );

  // Add plugin actions and filters
  add_action( 'admin_menu', 'iapost_automatic_admin_menu' );
  add_action( 'admin_enqueue_scripts', 'iapost_automatic_enqueue_admin_styles' );
  
}

// Run the plugin initialization function
add_action( 'plugins_loaded', 'iapost_automatic_init' );

function iapost_automatic_activate() {
    // Crear la tabla de configuraciones (ya implementado anteriormente)
    iapost_automatic_create_settings_table();

    // Establecer opciones predeterminadas (ejemplo)
    $default_settings = array(
        'post_types' => array('post'), // Tipos de publicaciones a considerar para publicación automática
        'default_social_networks' => array('facebook'), // Redes sociales seleccionadas por defecto
        // ... otras opciones de configuración
    );
    add_option( 'iapost_automatic_settings', $default_settings );

    // Otras tareas de inicialización (si es necesario)
    // ...
}

function iapost_automatic_deactivate() {
    // Eliminar tablas de la base de datos (opcional)
    global $wpdb;
    $table_name = $wpdb->prefix . 'iapost_api_settings';
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );

    // Eliminar opciones de configuración (opcional)
    delete_option( 'iapost_automatic_settings' );

    // Cerrar sesiones en APIs externas (si es necesario)
    // ...

    // Otras tareas de limpieza (si es necesario)
    // ...

    error_log( 'Plugin IAPost Automatic desactivado.' ); 
}

// Función para agregar enlaces de ajustes y documentación al plugin
function iapost_automatic_add_plugin_action_links( $links, $plugin_file ) {
    // Verificar si es nuestro plugin
    if ( $plugin_file == plugin_basename( __FILE__ ) ) {
        // Crear el enlace a la página de ajustes
        $settings_link = '<a href="' . admin_url( 'admin.php?page=iapost-automatic' ) . '">' . __( 'Ajustes', 'iapost-automatic' ) . '</a>';

        // Crear el enlace a la documentación (reemplaza con la URL real de tu documentación)
        $docs_link = '<a href="https://www.iapunto.com/iapost-automatic-docs" target="_blank">' . __( 'Documentación', 'iapost-automatic' ) . '</a>';

        // Agregar los enlaces al inicio del array de enlaces
        array_unshift( $links, $settings_link, $docs_link );
    }
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'iapost_automatic_add_plugin_action_links', 10, 2 );

// Function to add admin menu items
function iapost_automatic_admin_menu() {
    // Declarar la variable global dentro de la función
    global $iapost_automatic_admin_settings; 
    global $iapost_automatic_admin_calendar; 

    // Página principal del plugin (Dashboard)
    add_menu_page( 
        'IAPost Automatic', 
        'IAPost Automatic', 
        'manage_options',   
        'iapost-automatic', 
        'iapost_automatic_dashboard_page', // Nueva función para el dashboard
        'dashicons-share',  
        6                   
    );

    // Submenú de Calendario
    add_submenu_page(
        'iapost-automatic', 
        'Calendario', 
        'Calendario', 
        'manage_options', 
        'iapost-automatic-calendar', 
        array( $iapost_automatic_admin_calendar, 'render_calendar_page' ) // Utilizar el método de la clase AdminCalendar
    );

    // Submenú de Ajustes
    add_submenu_page(
        'iapost-automatic', 
        'Ajustes', 
        'Ajustes', 
        'manage_options', 
        'iapost-automatic-settings', // Cambiamos el slug para evitar conflictos
        array( $iapost_automatic_admin_settings, 'render_settings_page' ) 
    );

}

// Function to render the dashboard page
function iapost_automatic_dashboard_page() {
    // Verificar si el usuario tiene permisos suficientes
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'No tienes suficientes permisos para acceder a esta página.', 'iapost-automatic' ) );
    }

    // Fictional data for cards
    $total_posts = 25;
    $scheduled_posts = 10;
    $published_posts = 15;

    // Fictional data for weekly post count graph (replace with actual data)
    $weekly_post_counts = array(
        'Lunes' => 5,
        'Martes' => 3,
        'Miércoles' => 7,
        'Jueves' => 4,
        'Viernes' => 2,
        'Sábado' => 1,
        'Domingo' => 3,
    );

    // ... (rest of the function)

    echo '<div class="wrap">';
    echo '<h1>' . esc_html( get_admin_page_title() ) . '</h1>';

    // Cards with icons and numbers
    echo '<div class="iapost-automatic-dashboard-cards">';
    echo '<div class="card">';
    echo '<i class="fas fa-file-alt"></i>';
    echo '<h3>Total Posts</h3>'; 
    echo '<span class="card-data">' . $total_posts . '</span>';
    echo '</div>';

    echo '<div class="card">';
    echo '<i class="fas fa-calendar-alt"></i>';
    echo '<h3>Scheduled Posts</h3>'; 
    echo '<span class="card-data">' . $scheduled_posts . '</span>';
    echo '</div>';

    echo '<div class="card">';
    echo '<i class="fas fa-bullhorn"></i>';
    echo '<h3>Published Posts</h3>'; 
    echo '<span class="card-data">' . $published_posts . '</span>';
    echo '</div>';
    echo '</div>';

    // Weekly Post Count Graph (replace with actual data fetching logic)
    echo '<div class="iapost-automatic-dashboard-graph">';
    echo '<h2>Weekly Post Count</h2>';
    echo '<div class="chart-container">'; // Add a container for better responsiveness
    echo '<canvas id="weekly-post-count-chart"></canvas>';
    echo '</div>';
    echo '</div>';

    echo '</div>';

    // Incluir Chart.js y el script personalizado
  wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '1.0.0', true );
  wp_enqueue_script( 
      'iapost-automatic-dashboard-script', // Handle (identificador único)
      plugin_dir_url( __FILE__ ) . 'assets/js/script.js', // Ruta al archivo JS
      array( 'chartjs' ), // Dependencias (Chart.js en este caso)
      '1.0.0', // Versión (para control de caché)
      true     // Cargar en el footer (true)
  );
  
}

// Function to enqueue admin styles
function iapost_automatic_enqueue_admin_styles() {
    $screen = get_current_screen();

    // Verificar si estamos en la página de configuración del plugin
    if ( $screen->base === 'toplevel_page_iapost-automatic' ) { 
        wp_enqueue_style( 
            'iapost-automatic-admin-styles', 
            plugin_dir_url( __FILE__ ) . 'assets/css/style.css', 
            array(), 
            '1.0.0', 
            'all'    
        );
    }
}

// Función para crear la tabla de configuraciones 
function iapost_automatic_create_settings_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'iapost_api_settings';
    $charset_collate = $wpdb->get_charset_collate();

    // Verificar si la tabla ya existe
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            social_network varchar(50) NOT NULL, 
            app_id varchar(255) NOT NULL,
            app_secret varchar(255) NOT NULL,
            redirect_uri varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        // Agregar mensajes de depuración para verificar la creación de la tabla
        if ( $wpdb->last_error ) {
            error_log( 'Error al crear la tabla iapost_api_settings: ' . $wpdb->last_error );
        } else {
            error_log( 'Tabla iapost_api_settings creada correctamente.' );
        }
    } else {
        error_log( 'La tabla iapost_api_settings ya existe.' );
    }
}

