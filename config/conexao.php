<?php
require_once 'config.php';

class Conexao {
    private static $instance;
    
    public static function getConn() {
        if (!isset(self::$instance)) {
            try {
                self::$instance = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
                    DB_USER,
                    DB_PASS,
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
                );
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "Erro de conexão: " . $e->getMessage();
                exit;
            }
        }
        return self::$instance;
    }
}
