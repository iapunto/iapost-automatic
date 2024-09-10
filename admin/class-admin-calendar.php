<?php
/**
 * Admin Calendar Class for IAPost Automatic
 *
 * Handles the plugin's calendar page and functionalities.
 *
 * @package IAPost Automatic
 * @since 1.0.0
 */

class AdminCalendar {

    /**
     * Renderiza la página del calendario
     */
    public function render_calendar_page() {
        // Verificar si el usuario tiene permisos suficientes
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'No tienes suficientes permisos para acceder a esta página.', 'iapost-automatic' ) );
        }

        // Mostrar la página del calendario
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <p><?php _e( 'Aquí puedes ver y administrar las publicaciones programadas con IAPost Automatic.', 'iapost-automatic' ); ?></p>

            <div id='calendar'></div> 

            <script>
                // Inicialización básica de FullCalendar (reemplazar con la implementación real)
                document.addEventListener('DOMContentLoaded', function() {
                    var calendarEl = document.getElementById('calendar');
                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        events: <?php echo json_encode( $this->get_scheduled_posts() ); ?>,
                        eventClick: function(info) {
                            // Mostrar detalles de la publicación al hacer clic en un evento
                            // ... (implementar la lógica para mostrar detalles)
                            alert('Publicación: ' + info.event.title); 
                        }
                    });
                    calendar.render();
                });
            </script>
        </div>
        <?php
    }

    /**
     * Obtiene las publicaciones programadas
     *
     * @return array Arreglo de publicaciones programadas
     */
    public function get_scheduled_posts() {
        // Implementar la lógica para obtener las publicaciones programadas de la base de datos u otra fuente
        // ... (código para obtener las publicaciones programadas)

        // Ejemplo de retorno (reemplazar con la lógica real)
        return array(
            array(
                'title' => 'Publicación programada 1',
                'start' => date('Y-m-d H:i', strtotime( '+1 day' )), // programar para mañana
            ),
            array(
                'title' => 'Publicación programada 2',
                'start' => date('Y-m-d H:i', strtotime( '+3 days' )), // programar para 3 días a partir de ahora
            ),
        );
    }
}