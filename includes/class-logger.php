<?php
class WooImporterLogger {
    private $log_file;

    public function __construct() {
        // Ruta del archivo de log
        $this->log_file = plugin_dir_path(__FILE__) . '../woo-importer_log.txt';
    }

    // Función para registrar los mensajes
    public function log_message($level, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [$level] $message" . PHP_EOL;
        error_log($log_entry, 3, $this->log_file);
    }
}
