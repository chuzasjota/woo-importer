<?php
/*
Plugin Name: Woo Importer Products
Description: Plugin to import products from an external API to WooCommerce.
Version: 1.0
Author: Jhonatan Vanegas
*/

// Validación por si se ingresa directamente a la Url
if (!defined('ABSPATH')) {
    exit;
}

// Incluir el archivo de la clase login
require_once plugin_dir_path(__FILE__) . 'includes/class-login.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-product-importer.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-logger.php';


class WooImporterApi {

    private $woo_importer_login;
    private $woo_importer_product;
    private $woo_importer_logger;

    public function __construct() {
        // Instacias de clases
        $this->woo_importer_login = new WooImporterLogin();
        $this->woo_importer_product = new WooImporterProduct();
        $this->woo_importer_logger = new WooImporterLogger();

        // Hooks
        add_action('admin_menu', array($this, 'add_menu_item'));
        add_action('admin_init', array($this, 'login_logout'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_post_import_products', array($this, 'import_products')); // Handle product import
    }

    // Bootstrap CSS and JS
    public function enqueue_scripts() {
        wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', array(), null);
        wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    }

    // Añadir el item de menu dentro de WooCommerce seccion
    public function add_menu_item() {
        add_submenu_page(
            'woocommerce',
            'Import Products',  // Page title
            'Import Products',  // Menu title
            'manage_options',
            'woo-importer',   // Menu slug
            array($this, 'login_page')
        );
    }

    // Mostrar la pagina de login en el admin principal
    public function login_page() {
        if (isset($_GET['import_success'])) {
            $success = sanitize_text_field($_GET['import_success']);
            $this->show_message($success);
        }
        // Validación para mostrar la sección cuando este logueado
        if ($this->woo_importer_login->is_logged_in()) {
            echo '<div class="container mt-5">';
            echo '<h2 class="mb-4">Bienvenido, ' . esc_html($this->woo_importer_login->get_username()) . '</h2>';
            echo '<div class="row">';
            echo '<div class="col">';
            // Botón para importar productos
            echo '<form method="POST" action="' . esc_url(admin_url('admin-post.php')) . '">';
            echo '<input type="hidden" name="action" value="import_products">';
            echo '<button type="submit" class="btn btn-primary">Importar productos desde FakeStore</button>';
            echo '</form>';
            echo '</div>';

            echo '<div class="col">';
            echo '<a href="' . esc_url(add_query_arg('woo_importer_logout', 'true', admin_url('admin.php?page=woo-importer'))) . '" class="btn btn-danger">Logout</a>';
            echo '</div>';

            echo '</div>';
        } else {
            // Si no esta logueado muestra el formulario para hacer login
        ?>
            <div class="container mt-5">
                <h2 class="mb-4">Login to FakeStore API</h2>
                <div class="row">
                    <div class="col-6">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="woo_importer_username" class="form-label">Username</label>
                                <input type="text" id="woo_importer_username" name="woo_importer_username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="woo_importer_password" class="form-label">Password</label>
                                <input type="password" id="woo_importer_password" name="woo_importer_password" class="form-control" required>
                            </div>
                            <button type="submit" name="woo_importer_submit" class="btn btn-primary">Login</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    // funcion que muestra el toast si la importación es correcta o no
    private function show_message($success){
        $imported = isset($_GET['imported']) ? intval($_GET['imported']) : 0;
        $updated = isset($_GET['updated']) ? intval($_GET['updated']) : 0;

        $message = '';
        if ($success === 'true') {
            $message = 'Productos importados Exitosamente! Importados: ' . $imported . ', Actualizados: ' . $updated;
            $toast_class = 'bg-success';
        } else {
            $message = 'Importacion Fallida.';
            $toast_class = 'bg-danger';
        }
    
        echo '
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="importToast" class="toast align-items-center ' . esc_attr($toast_class) . ' text-white" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ' . esc_html($message) . '
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var toastEl = document.getElementById("importToast");
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            });
        </script>
        ';
    }

    // Funcion para procesar login o logout
    public function login_logout() {
        $this->woo_importer_login->init_session();

        // Validación logout
        if (isset($_GET['woo_importer_logout']) && $_GET['woo_importer_logout'] == 'true') {
            $this->woo_importer_login->logout();
            wp_safe_redirect(admin_url('admin.php?page=woo-importer'));
            exit;
        }

        // Validacion login
        if (isset($_POST['woo_importer_submit'])) {
            // Tomar los datos de los inputs
            $username = sanitize_text_field($_POST['woo_importer_username']);
            $password = sanitize_text_field($_POST['woo_importer_password']);

            // Llamar a la funcion login, enviando los datos del form
            $login_successful = $this->woo_importer_login->login($username, $password);

            if ($login_successful) {
                wp_safe_redirect(admin_url('admin.php?page=woo-importer'));
                $this->woo_importer_logger->log_message("Success", "Login Correcto!");
                exit;
            } else {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error is-dismissible"><p>Error de Login, Por favor ingrese los datos correctos</p></div>';
                    $this->woo_importer_logger->log_message("Error", "Error de Login, Por favor ingrese los datos correctos");
                });
            }
        }
    }

    // Funcion que llama la importacion de prductos
    public function import_products() {
        //Valdacion si esta logueado para poder hacer el llamado
        if (!$this->woo_importer_login->is_logged_in()) {
            return;
        }

        // Llamar a la funcion importar productos
        $success = $this->woo_importer_product->import_products();

        if ($success) {
            wp_redirect(admin_url('admin.php?page=woo-importer&import_success=true&imported=' . $success['imported'] . '&updated=' . $success['updated']));
        } else {
            wp_redirect(admin_url('admin.php?page=woo-importer&import_success=false'));
        }
        exit;
    }
}

// Instanciar clase principal
new WooImporterApi();
