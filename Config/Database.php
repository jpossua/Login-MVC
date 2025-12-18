<?php

/**
 * ============================================
 * CONFIGURACIÓN DE BASE DE DATOS
 * ============================================
 * 
 * Esta clase maneja la conexión a la base de datos usando PDO.
 * PDO (PHP Data Objects) es la forma recomendada de conectar a bases de datos
 * porque soporta consultas preparadas que previenen SQL Injection.
 * 
 * Para usar en otro proyecto, modificar:
 * - $host: Servidor de base de datos
 * - $db_name: Nombre de la base de datos
 * - $username: Usuario de la base de datos
 * - $password: Contraseña del usuario
 */

class Database
{
    // ============================================
    // CONFIGURACIÓN DE CONEXIÓN
    // ============================================

    /**
     * Host del servidor de base de datos
     * En desarrollo local suele ser 'localhost'
     * En producción sería la IP o nombre del servidor
     */
    private $host = 'localhost';

    /**
     * Nombre de la base de datos
     */
    private $db_name = 'login-php';

    /**
     * Usuario de la base de datos
     * IMPORTANTE: En producción, usar un usuario con permisos limitados
     * Solo debe tener permisos de SELECT, INSERT, UPDATE en las tablas necesarias
     */
    private $username = 'LoginPhp';

    /**
     * Contraseña del usuario de base de datos
     * IMPORTANTE: En producción, NO hardcodear la contraseña aquí
     * Usar variables de entorno o archivo de configuración externo
     */
    private $password = '95f90HZJy3sb';

    /**
     * Objeto PDO para la conexión
     */
    public $PDO;

    // ============================================
    // MÉTODO: GET CONNECTION
    // ============================================

    /**
     * Establece y devuelve la conexión a la base de datos
     * 
     * Configuración de PDO:
     * - PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
     *   Hace que PDO lance excepciones en caso de error,
     *   lo que permite un mejor manejo de errores.
     * 
     * @return PDO|null Objeto PDO conectado o null si hay error
     */
    public function getConnection()
    {
        $this->PDO = null;

        try {
            // ============================================
            // CREAR CONEXIÓN PDO
            // ============================================
            /**
             * DSN (Data Source Name) para MySQL/MariaDB:
             * mysql:host=SERVIDOR;dbname=BASE_DE_DATOS
             * 
             * Opciones adicionales recomendadas:
             * - charset=utf8mb4 para soporte completo de Unicode
             */
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";

            $this->PDO = new PDO($dsn, $this->username, $this->password);
            
            // ============================================
            // CONFIGURAR MODO DE ERRORES
            // ============================================
            /**
             * ERRMODE_EXCEPTION hace que PDO lance excepciones
             * en lugar de warnings silenciosos.
             * Esto es crucial para detectar problemas de SQL.
             */
            $this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // ============================================
            // CONFIGURAR FETCH MODE POR DEFECTO
            // ============================================
            /**
             * FETCH_ASSOC hace que fetch() devuelva arrays asociativos
             * en lugar de arrays numéricos.
             */
            $this->PDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            // ============================================
            // MANEJO DE ERRORES DE CONEXIÓN
            // ============================================
            /**
             * En producción, NO mostrar detalles del error al usuario.
             * Registrar en log y mostrar mensaje genérico.
             */
            error_log("Error de conexión a BD: " . $exception->getMessage());
            echo '<div class="alert alert-danger text-center m-5" role="alert">
                    Error de conexión a la base de datos. Por favor, contacte al administrador.
                  </div>';
        }

        return $this->PDO;
    }
}
