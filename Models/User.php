<?php

/**
 * ============================================
 * MODELO DE USUARIO (Usuario)
 * ============================================
 * 
 * Este modelo maneja todas las operaciones con la base de datos
 * relacionadas con los usuarios:
 * - Verificar credenciales (login)
 * - Registrar nuevos usuarios
 * 
 * Características de seguridad implementadas:
 * - Consultas preparadas con PDO (previene SQL Injection)
 * - Verificación de contraseñas hasheadas
 * - Campo 'admitido' para control de acceso
 */

require_once 'Config/Database.php';

class Usuario
{
    /**
     * Conexión PDO a la base de datos
     * @var PDO
     */
    private $PDO;

    /**
     * Nombre de la tabla de usuarios
     * @var string
     */
    private $tabla_nombre = "usuarios";

    // ============================================
    // CONSTRUCTOR
    // ============================================

    /**
     * Constructor del modelo
     * Inicializa la conexión a la base de datos usando la clase Database
     */
    public function __construct()
    {
        $database = new Database();
        $this->PDO = $database->getConnection();
    }

    // ============================================
    // MÉTODO: LOGIN (Verificar credenciales)
    // ============================================

    /**
     * Verifica las credenciales del usuario
     * 
     * Proceso de verificación segura:
     * 1. Buscar usuario por idUser usando consulta preparada
     * 2. Si existe, verificar contraseña hasheada con password_verify()
     * 3. Devolver datos del usuario si las credenciales son correctas
     * 
     * IMPORTANTE: La contraseña NO se compara directamente.
     * Se usa password_verify() que compara el hash almacenado
     * con el hash de la contraseña proporcionada.
     */
    public function login($idUser, $password)
    {
        // ============================================
        // PASO 1: PREPARAR CONSULTA SEGURA
        // ============================================
        /**
         * Usamos consultas preparadas (prepared statements) con PDO.
         * El signo ? es un placeholder que será reemplazado de forma segura.
         * Esto previene ataques de SQL Injection porque los datos
         * nunca se concatenan directamente en la consulta.
         */
        $query = "SELECT * FROM " . $this->tabla_nombre . " WHERE idUser = ? LIMIT 0,1";

        // ============================================
        // PASO 2: PREPARAR Y EJECUTAR
        // ============================================
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(1, $idUser);
        $stmt->execute();

        // ============================================
        // PASO 3: VERIFICAR SI EL USUARIO EXISTE
        // ============================================
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // ============================================
            // PASO 4: VERIFICAR CONTRASEÑA HASHEADA
            // ============================================
            /**
             * password_verify() compara la contraseña proporcionada
             * con el hash almacenado en la base de datos.
             * 
             * El hash incluye:
             * - El algoritmo usado (bcrypt por defecto)
             * - El salt (generado automáticamente)
             * - El hash resultante
             * 
             * password_verify() extrae estos componentes y realiza
             * la comparación de forma segura.
             */
            if (password_verify($password, $row['password'])) {
                // Credenciales correctas: devolver datos del usuario
                // Incluimos 'admitido' para verificar en el controlador
                return $row;
            }
        }

        // Usuario no encontrado o contraseña incorrecta
        return false;
    }

    // ============================================
    // MÉTODO: REGISTER (Registrar nuevo usuario)
    // FUNCIONALIDAD OPCIONAL - Puntos 9 y 10 del ejercicio
    // ============================================

    /**
     * Registra un nuevo usuario en la base de datos
     * 
     * Proceso de registro:
     * 1. Verificar que el usuario no exista
     * 2. Insertar con admitido=0 (pendiente de aprobación)
     * 
     * IMPORTANTE: La contraseña debe llegar ya hasheada desde el controlador.
     * El hasheo se hace en el controlador para mantener la separación de responsabilidades.
     * 
     * @param string $idUser ID del nuevo usuario
     * @param string $passwordHash Contraseña YA HASHEADA con password_hash()
     * @param string $nombre Nombre del usuario
     * @param string $apellidos Apellidos del usuario
     * @return bool true si se registró correctamente, false en caso contrario
     */
    public function register($idUser, $passwordHash, $nombre, $apellidos)
    {
        // ============================================
        // PASO 1: VERIFICAR SI EL USUARIO YA EXISTE
        // ============================================
        /**
         * Antes de insertar, verificamos que el idUser no esté en uso.
         * Esto previene errores de clave duplicada y proporciona
         * un mensaje de error más amigable.
         */
        $checkQuery = "SELECT idUser FROM " . $this->tabla_nombre . " WHERE idUser = ? LIMIT 1";
        $checkStmt = $this->PDO->prepare($checkQuery);
        $checkStmt->bindParam(1, $idUser);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            // El usuario ya existe
            return false;
        }

        // ============================================
        // PASO 2: INSERTAR NUEVO USUARIO
        // ============================================
        /**
         * Insertamos el nuevo usuario con admitido=0.
         * Esto significa que el usuario está registrado pero NO puede
         * iniciar sesión hasta que un administrador lo apruebe.
         * 
         * Campos:
         * - idUser: Identificador único del usuario
         * - password: Contraseña HASHEADA (nunca en texto plano)
         * - nombre: Nombre del usuario
         * - apellidos: Apellidos del usuario
         * - admitido: 0 = pendiente, 1 = aprobado
         */
        $query = "INSERT INTO " . $this->tabla_nombre .
            " (idUser, password, nombre, apellidos, admitido) VALUES (?, ?, ?, ?, 0)";

        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(1, $idUser);
        $stmt->bindParam(2, $passwordHash);
        $stmt->bindParam(3, $nombre);
        $stmt->bindParam(4, $apellidos);

        // Ejecutar y devolver resultado
        return $stmt->execute();
    }

    // ============================================
    // MÉTODO: CHECK USER EXISTS (Verificar existencia)
    // ============================================

    /**
     * Verifica si un usuario existe en la base de datos
     * 
     * @param string $idUser ID del usuario a verificar
     * @return bool true si existe, false si no
     */
    public function userExists($idUser)
    {
        $query = "SELECT idUser FROM " . $this->tabla_nombre . " WHERE idUser = ? LIMIT 1";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(1, $idUser);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
