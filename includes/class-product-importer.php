<?php
require_once plugin_dir_path(__FILE__) . 'class-logger.php';
class WooImporterProduct {
    private $api_url = 'https://fakestoreapi.com/products';
    private $imported_count = 0;
    private $updated_count = 0;

    public function __construct() {
        // Instacias de clases
        $this->woo_importer_logger = new WooImporterLogger();
    }

    public function import_products() {
        // Obtener los productos de la API
        $response = wp_remote_get($this->api_url);

        if (is_wp_error($response)) {
            $this->woo_importer_logger->log_message("Error", "Error al consumir el API");
            return false; // Error en la solicitud
        }

        $products = json_decode(wp_remote_retrieve_body($response));

        // Procesar cada producto y agregarlo a WooCommerce
        foreach ($products as $product) {
            $this->create_or_update_product($product);
        }

        return ['success' => true, 'imported' => $this->imported_count, 'updated' => $this->updated_count];
    }

    private function create_or_update_product($product) {
        $existing_product_id = wc_get_product_id_by_sku($product->id);
      
        if ($existing_product_id) {
            // Actualizar producto existente
            $wc_product = new WC_Product($existing_product_id);
            $this->updated_count++; // Incrementar contador de actualizados
            $this->woo_importer_logger->log_message("Update Product", 'Producto actualizado: ' . $product->title);
        } else {
            // Crear nuevo producto
            $wc_product = new WC_Product();
            $this->$imported_count++; // Incrementar contador de importados
            $this->woo_importer_logger->log_message("Add Product", 'Producto Importado: ' . $product->title);
        }

        // Asignar los datos del producto
        $wc_product->set_name($product->title);
        // Convertir el precio de "." a "," para WooCommerce
        $price = str_replace('.', ',', $product->price);
        $wc_product->set_regular_price($price);
        $wc_product->set_description($product->description);
        $wc_product->set_sku($product->id); // Usar ID de la API como SKU

        // Asignar categoría (si existe)
        if (isset($product->category)) {
            $category_id = $this->get_or_create_category($product->category);
            if ($category_id) {
                $wc_product->set_category_ids(array($category_id));
            }
        }

        // Cargar imagen
        if (isset($product->image)) {
            $image_url = $product->image;
            $image_id = $this->upload_image($image_url);
            if ($image_id) {
                $wc_product->set_image_id($image_id);
            }else{
                // $this->log('Error al guardar la imagen: ' . $product->title);
                echo '<div class="notice notice-error is-dismissible"><p>Error al guardar la imagen</p></div>';
                $this->woo_importer_logger->log_message("Error", "Error al guardar la imagen");
            }
        }

        // Guardar producto en WooCommerce
        try {
            $wc_product->save();
        } catch (Exception $e) {
            echo "<div class='notice notice-error is-dismissible'><p>Error al guardar el producto: {$e->getMessage()}</p></div>";
            $this->woo_importer_logger->log_message("Error", 'Error al guardar el producto: ' . $e->getMessage());
        }
    }

     // Función para crear o asignar una categoría
     private function get_or_create_category($category_name) {
        $term = term_exists($category_name, 'product_cat');

        if ($term !== 0 && $term !== null) {
            return $term['term_id']; // La categoría ya existe
        } else {
            // Crear la categoría
            $new_term = wp_insert_term($category_name, 'product_cat');
            if (!is_wp_error($new_term)) {
                return $new_term['term_id']; // Retornar el ID de la nueva categoría
            }
        }
        return false;
    }

    // Función para cargar una imagen y retornarla como ID de adjunto
    private function upload_image($image_url) {
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);
        $filename = basename($image_url);

        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $file);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;
    }
}
