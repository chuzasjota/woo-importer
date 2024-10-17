<?php
class WooImporterLogin {

    // Iniciar variables de session
    public function init_session() {
        if (!session_id()) {
            session_start();
        }
    }

    // Validar si esta logueado
    public function is_logged_in() {
        return isset($_SESSION['fake_user']);
    }

    // Get username Fake user 
    public function get_username() {
        return $_SESSION['fake_user'];
    }

    // Validar usuario desde la API
    public function login($username, $password) {
        $response = wp_remote_post('https://fakestoreapi.com/auth/login', array(
            'body' => json_encode(array(
                'username' => $username,
                'password' => $password
            )),
            'headers' => array('Content-Type' => 'application/json')
        ));

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if (isset($data->token)) {
            // Login correcto, guardamos variable de session
            $_SESSION['fake_user'] = $username;
            return true;
        } else {
            return false;
        }
    }

    // Logout funcion
    public function logout() {
        unset($_SESSION['fake_user']);
    }
}

