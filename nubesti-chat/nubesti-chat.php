<?php
/*
Plugin Name: Nubesti Chat
Plugin URI: https://nubesti.com
Description: Un plugin simple para integrar funcionalidades de chat como WhatsApp en tu sitio de WordPress.
Version: 1.4
Author: Nubesti LLC
Author URI: https://nubesti.com
License: GPLv2 or later
Text Domain: nubesti-chat
*/

if (!defined('ABSPATH')) {
    exit; // Salida si se accede directamente
}

// Función que se ejecuta al activar el plugin
function nubesti_chat_activate() {
    $default_options = array(
        'nubesti_chat_enabled' => true,
        'nubesti_chat_phone_number' => '',
        'nubesti_chat_welcome_message' => '¡Hola! ¿Cómo podemos ayudarte?',
        'nubesti_chat_widget_position' => 'bottom_right',
        'nubesti_chat_widget_design' => 'default',
    );

    foreach ($default_options as $key => $value) {
        if (get_option($key) === false) {
            add_option($key, $value);
        }
    }
}

register_activation_hook(__FILE__, 'nubesti_chat_activate');

// Función que se ejecuta al desactivar el plugin
function nubesti_chat_deactivate() {
    $options_to_remove = array(
        'nubesti_chat_enabled',
        'nubesti_chat_phone_number',
        'nubesti_chat_welcome_message',
        'nubesti_chat_widget_position',
        'nubesti_chat_widget_design',
    );

    foreach ($options_to_remove as $option) {
        delete_option($option);
    }
}

register_deactivation_hook(__FILE__, 'nubesti_chat_deactivate');

// Agregar el menú de configuración del plugin
function nubesti_chat_add_admin_menu() {
    add_menu_page(
        'Nubesti Chat Settings',
        'Nubesti Chat',
        'manage_options',
        'nubesti-chat-settings',
        'nubesti_chat_settings_page',
        'dashicons-admin-comments',
        99
    );
}

add_action('admin_menu', 'nubesti_chat_add_admin_menu');

// Función que renderiza la página de configuración
function nubesti_chat_settings_page() {
    ?>
    <div class="wrap">
        <h2>Nubesti Chat Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('nubesti-chat-settings-group');
            do_settings_sections('nubesti-chat-settings-group');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Habilitar Chat:</th>
                    <td><input type="checkbox" name="nubesti_chat_enabled" value="1" <?php checked(get_option('nubesti_chat_enabled'), 1); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Número de WhatsApp:</th>
                    <td><input type="text" name="nubesti_chat_phone_number" value="<?php echo esc_attr(get_option('nubesti_chat_phone_number')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Mensaje de Bienvenida:</th>
                    <td><input type="text" name="nubesti_chat_welcome_message" value="<?php echo esc_attr(get_option('nubesti_chat_welcome_message')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Posición del Widget:</th>
                    <td>
                        <select name="nubesti_chat_widget_position">
                            <option value="bottom_left" <?php selected(get_option('nubesti_chat_widget_position'), 'bottom_left'); ?>>Izquierda Inferior</option>
                            <option value="bottom_center" <?php selected(get_option('nubesti_chat_widget_position'), 'bottom_center'); ?>>Centro Inferior</option>
                            <option value="bottom_right" <?php selected(get_option('nubesti_chat_widget_position'), 'bottom_right'); ?>>Derecha Inferior</option>
                            <option value="center_left" <?php selected(get_option('nubesti_chat_widget_position'), 'center_left'); ?>>Izquierda Centro</option>
                            <option value="center_right" <?php selected(get_option('nubesti_chat_widget_position'), 'center_right'); ?>>Derecha Centro</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Diseño del Widget:</th>
                    <td>
                        <select name="nubesti_chat_widget_design">
                            <option value="default" <?php selected(get_option('nubesti_chat_widget_design'), 'default'); ?>>Diseño Predeterminado</option>
                            <option value="minimal" <?php selected(get_option('nubesti_chat_widget_design'), 'minimal'); ?>>Diseño Minimalista</option>
                            <option value="modern" <?php selected(get_option('nubesti_chat_widget_design'), 'modern'); ?>>Diseño Moderno</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Registrar nuestras opciones
function nubesti_chat_register_settings() {
    register_setting('nubesti-chat-settings-group', 'nubesti_chat_enabled');
    register_setting('nubesti-chat-settings-group', 'nubesti_chat_phone_number');
    register_setting('nubesti-chat-settings-group', 'nubesti_chat_welcome_message');
    register_setting('nubesti-chat-settings-group', 'nubesti_chat_widget_position');
    register_setting('nubesti-chat-settings-group', 'nubesti_chat_widget_design');
}

add_action('admin_init', 'nubesti_chat_register_settings');

// Función para mostrar el chat en el frontend
function nubesti_chat_display() {
    if (get_option('nubesti_chat_enabled') != '1') {
        return;
    }

    $phone_number = get_option('nubesti_chat_phone_number');
    $welcome_message = get_option('nubesti_chat_welcome_message');
    $widget_position = get_option('nubesti_chat_widget_position', 'bottom_right');
    $widget_design = get_option('nubesti_chat_widget_design', 'default');

    if (empty($phone_number)) {
        return;
    }

    $whatsapp_url = "https://wa.me/" . $phone_number . "?text=" . urlencode($welcome_message);
    $position_style = '';

    switch ($widget_position) {
        case 'bottom_left':
            $position_style = 'left: 20px; bottom: 20px;';
            break;
        case 'bottom_center':
            $position_style = 'left: 50%; bottom: 20px; transform: translateX(-50%);';
            break;
        case 'bottom_right':
            $position_style = 'right: 20px; bottom: 20px;';
            break;
        case 'center_left':
            $position_style = 'left: 20px; top: 50%; transform: translateY(-50%);';
            break;
        case 'center_right':
            $position_style = 'right: 20px; top: 50%; transform: translateY(-50%);';
            break;
    }

    $additional_class = 'nubesti-chat-' . $widget_design;

    echo '<a href="' . esc_url($whatsapp_url) . '" target="_blank" class="nubesti-chat-button ' . esc_attr($additional_class) . '" style="' . $position_style . '">Chatea con nosotros</a>';
}

add_action('wp_footer', 'nubesti_chat_display');

// Función para encolar los estilos del plugin
function nubesti_chat_enqueue_styles() {
    $css_file = plugin_dir_url(__FILE__) . 'css/nubesti-chat-styles.css';
    wp_enqueue_style('nubesti-chat-styles', $css_file);
}

add_action('wp_enqueue_scripts', 'nubesti_chat_enqueue_styles');
