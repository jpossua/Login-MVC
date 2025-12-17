<?php

/**
 * ============================================
 * CONTROLADOR DE AUTENTICACIÓN (AuthController)
 * ============================================
 * 
 * Este controlador maneja todas las operaciones relacionadas con
 * la autenticación de usuarios:
 * - Login / Logout
 * - Registro de nuevos usuarios
 * - Acceso al dashboard
 * 
 * Características de seguridad implementadas:
 * - Verificación de token CSRF en todas las operaciones POST
 * - Sanitización de datos de entrada
 * - Control de límite de intentos de login
 * - Hasheo seguro de contraseñas
 * - Eliminación segura de cookies en logout
 */

class AuthController
{
    /**
     * Modelo de usuario para operaciones con la base de datos
     * @var Usuario
     */
    private $userModel;

    // ============================================
    // CONSTRUCTOR
    // ============================================

    /**
     * Constructor del controlador
     * Inicializa el modelo de usuario que maneja la conexión a la BD
     */
    public function __construct()
    {
        $this->userModel = new Usuario();
    }

    // ============================================
    // MÉTODO: LOGIN (Mostrar formulario)
    // ============================================

    /**
     * Muestra la vista del formulario de login
     * 
     * Esta es la página de entrada a la aplicación.
     * El formulario incluye un campo oculto con el token CSRF.
     */
    public function login()
    {
        // Carga la vista del formulario de login
        include 'Views/login.php';
    }

    // ============================================
    // MÉTODO: AUTHENTICATE (Procesar login)
    // ============================================

    /**
     * Procesa el formulario de login
     * 
     * Flujo de autenticación segura:
     * 1. Verificar que sea una petición POST
     * 2. Verificar token CSRF
     * 3. Verificar que el usuario no esté bloqueado por intentos fallidos
     * 4. Sanitizar datos de entrada
     * 5. Consultar usuario en la base de datos
     * 6. Verificar contraseña hasheada
     * 7. Verificar que el usuario esté admitido
     * 8. Guardar datos en sesión y redirigir
     */
    public function authenticate()
    {
        // ============================================
        // PASO 1: VERIFICAR MÉTODO POST
        // ============================================
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: index.php?action=login');
            exit();
        }

        // ============================================
        // PASO 2: VERIFICAR TOKEN CSRF
        // ============================================
        /**
         * El token CSRF previene ataques donde un sitio malicioso
         * intenta enviar formularios en nombre del usuario.
         * Si el token no coincide, rechazamos la petición.
         */
        if (!SecurityHelper::validateCSRFToken()) {
            $_SESSION['error'] = "Token CSRF no válido. Por favor, recarga la página e intenta de nuevo.";
            header('Location: index.php?action=login');
            exit();
        }

        // ============================================
        // PASO 3: VERIFICAR LÍMITE DE INTENTOS
        // ============================================
        /**
         * Protección contra ataques de fuerza bruta.
         * Si el usuario ha fallado demasiadas veces, lo bloqueamos temporalmente.
         */
        $attemptCheck = SecurityHelper::checkLoginAttempts();
        if ($attemptCheck['blocked']) {
            $_SESSION['error'] = $attemptCheck['message'];
            header('Location: index.php?action=login');
            exit();
        }

        // ============================================
        // PASO 4: SANITIZAR DATOS DE ENTRADA
        // ============================================
        /**
         * Sanitizamos los datos antes de usarlos.
         * Esto previene ataques XSS y otros tipos de inyección.
         */
        $username = SecurityHelper::sanitizeInput($_POST['idUser'] ?? '');
        $password = $_POST['password'] ?? '';  // No sanitizamos password para no alterar caracteres especiales

        // ============================================
        // PASO 5: CONSULTAR USUARIO EN BASE DE DATOS
        // ============================================
        /**
         * El modelo Usuario usa PDO con consultas preparadas,
         * lo que previene ataques de inyección SQL.
         */
        $user = $this->userModel->login($username, $password);

        if ($user) {
            // ============================================
            // PASO 6: VERIFICAR CAMPO 'ADMITIDO' (OPCIONAL - Puntos 9 y 10)
            // ============================================
            /**
             * FUNCIONALIDAD OPCIONAL (Ejercicio anterior - Puntos 9 y 10):
             * - Punto 9: Registro de usuario en un solo paso
             * - Punto 10: Auto-registro con autorización de admin (campo 'admitido')
             * 
             * Los usuarios nuevos se crean con admitido=0 hasta que
             * un administrador los apruebe (cambie a admitido=1).
             */
            if (isset($user['admitido']) && $user['admitido'] != 1) {
                $_SESSION['error'] = "Tu cuenta está pendiente de aprobación por el administrador.";
                header('Location: index.php?action=login');
                exit();
            }

            // ============================================
            // PASO 7: LOGIN EXITOSO
            // ============================================
            /**
             * Login exitoso:
             * 1. Resetear contador de intentos fallidos
             * 2. Regenerar ID de sesión (previene session fixation)
             * 3. Guardar datos del usuario en sesión
             * 4. Redirigir al dashboard
             */

            // Resetear contador de intentos
            SecurityHelper::resetLoginAttempts();

            // Regenerar ID de sesión por seguridad
            session_regenerate_id(true);

            // Guardar datos del usuario en sesión
            $_SESSION['idUser'] = $username;
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['apellidos'] = $user['apellidos'];
            $_SESSION['usuario_logueado'] = true;

            // Redirigir al dashboard
            header('Location: index.php?action=dashboard');
            exit();
        } else {
            // ============================================
            // LOGIN FALLIDO
            // ============================================
            /**
             * Credenciales incorrectas:
             * 1. Incrementar contador de intentos fallidos
             * 2. Mostrar mensaje de error genérico
             *    (no indicamos si el usuario existe o no por seguridad)
             */
            SecurityHelper::incrementLoginAttempts();
            $_SESSION['error'] = "Usuario o contraseña incorrectos.";
            header('Location: index.php?action=login');
            exit();
        }
    }

    // ============================================
    // MÉTODO: DASHBOARD (Página protegida)
    // ============================================

    /**
     * Muestra la página de bienvenida/dashboard
     * 
     * Esta página solo es accesible para usuarios autenticados.
     * Si no hay sesión activa, redirige al login.
     */
    public function dashboard()
    {
        // Verificar si el usuario ha iniciado sesión
        if (!isset($_SESSION['idUser'])) {
            header('Location: index.php?action=login');
            exit();
        }

        // Carga la vista del dashboard (página de bienvenida)
        include 'Views/dashboard.php';
    }

    // ============================================
    // MÉTODO: LOGOUT (Cierre de sesión seguro)
    // ============================================

    /**
     * Cierra la sesión de forma segura
     * 
     * Proceso de logout seguro:
     * 1. Limpiar todas las variables de sesión
     * 2. Destruir la sesión
     * 3. Eliminar la cookie de sesión del navegador
     * 4. Redirigir al login
     * 
     * Es importante eliminar la cookie DESPUÉS de destruir la sesión
     * para asegurar que no queden rastros de la sesión.
     */
    public function logout()
    {
        // ============================================
        // PASO 1: LIMPIAR VARIABLES DE SESIÓN
        // ============================================
        session_unset();

        // ============================================
        // PASO 2: DESTRUIR LA SESIÓN
        // ============================================
        session_destroy();
        
        // ============================================
        // PASO 3: ELIMINAR COOKIE DE SESIÓN
        // ============================================
        /**
         * Eliminamos explícitamente la cookie de sesión.
         * Esto se hace estableciendo una fecha de expiración en el pasado.
         * 
         * Usamos los mismos parámetros que se usaron al crear la cookie
         * para asegurar que se elimine correctamente.
         */
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),         // Nombre de la cookie (generalmente PHPSESSID)
                '',                     // Valor vacío
                time() - 42000,         // Fecha de expiración en el PASADO
                $params["path"],        // Misma ruta que la original
                $params["domain"],      // Mismo dominio
                $params["secure"],      // Mismo valor de secure
                $params["httponly"]     // Mismo valor de httponly
            );
        }

        // ============================================
        // PASO 4: REDIRIGIR AL LOGIN
        // ============================================
        header('Location: index.php?action=login');
        exit();
    }

    // ============================================
    // MÉTODO: SHOW REGISTER (OPCIONAL - Punto 9)
    // ============================================
    // FUNCIONALIDAD OPCIONAL del ejercicio anterior:
    // Punto 9: Programad un posible registro de usuario (en un solo paso)

    /**
     * Muestra la vista del formulario de registro
     * 
     * El formulario incluye un campo oculto con el token CSRF.
     */
    public function showRegister()
    {
        // Carga la vista del formulario de registro
        include 'Views/registro.php';
    }

    // ============================================
    // MÉTODO: REGISTER (OPCIONAL - Puntos 9 y 10)
    // ============================================
    // FUNCIONALIDAD OPCIONAL del ejercicio anterior:
    // Punto 9: Programad un posible registro de usuario (en un solo paso)
    // Punto 10: Auto-registro con autorización de admin (campo 'admitido')

    /**
     * Procesa el formulario de registro de nuevo usuario
     * 
     * Flujo de registro seguro:
     * 1. Verificar que sea una petición POST
     * 2. Verificar token CSRF
     * 3. Sanitizar datos de entrada
     * 4. Validar datos (servidor)
     * 5. Hashear la contraseña
     * 6. Insertar en base de datos con admitido=0 (Punto 10)
     * 7. Redirigir con mensaje de éxito
     */
    public function register()
    {
        // ============================================
        // PASO 1: VERIFICAR MÉTODO POST
        // ============================================
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: index.php?action=showRegister');
            exit();
        }

        // ============================================
        // PASO 2: VERIFICAR TOKEN CSRF
        // ============================================
        if (!SecurityHelper::validateCSRFToken()) {
            $_SESSION['registro_error'] = "Token CSRF no válido. Por favor, recarga la página e intenta de nuevo.";
            header('Location: index.php?action=showRegister');
            exit();
        }

        // ============================================
        // PASO 3: SANITIZAR DATOS DE ENTRADA
        // ============================================
        $idUser = SecurityHelper::sanitizeInput($_POST['idUser'] ?? '');
        $password = $_POST['password'] ?? '';  // No sanitizar para preservar caracteres especiales
        $nombre = SecurityHelper::sanitizeInput($_POST['nombre'] ?? '');
        $apellidos = SecurityHelper::sanitizeInput($_POST['apellidos'] ?? '');

        // ============================================
        // PASO 4: VALIDACIONES DEL SERVIDOR
        // ============================================
        /**
         * Validamos en el servidor además del cliente (JavaScript)
         * porque NUNCA debemos confiar solo en la validación del cliente.
         * Un atacante puede desactivar JavaScript o enviar peticiones directas.
         */

        // Validar que todos los campos estén completos
        if (empty($idUser) || empty($password) || empty($nombre) || empty($apellidos)) {
            $_SESSION['registro_error'] = "Todos los campos son obligatorios.";
            header('Location: index.php?action=showRegister');
            exit();
        }

        // Validar ID de usuario
        $idUserValidation = SecurityHelper::validateIdUser($idUser);
        if (!$idUserValidation['valid']) {
            $_SESSION['registro_error'] = implode(" ", $idUserValidation['errors']);
            header('Location: index.php?action=showRegister');
            exit();
        }

        // Validar contraseña
        $passwordValidation = SecurityHelper::validatePassword($password);
        if (!$passwordValidation['valid']) {
            $_SESSION['registro_error'] = implode(" ", $passwordValidation['errors']);
            header('Location: index.php?action=showRegister');
            exit();
        }

        // ============================================
        // PASO 5: HASHEAR CONTRASEÑA
        // ============================================
        /**
         * NUNCA almacenamos contraseñas en texto plano.
         * password_hash() usa bcrypt por defecto, que es:
         * - Lento (dificulta ataques de fuerza bruta)
         * - Incluye salt automáticamente
         * - Resistente a ataques de rainbow tables
         */
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // ============================================
        // PASO 6: INTENTAR REGISTRAR USUARIO
        // ============================================
        /**
         * El modelo insertará el usuario con admitido=0
         * Un administrador deberá aprobarlo para que pueda iniciar sesión.
         */
        if ($this->userModel->register($idUser, $passwordHash, $nombre, $apellidos)) {
            $_SESSION['registro_exito'] = "Registro exitoso. Tu cuenta está pendiente de aprobación por el administrador.";
            header('Location: index.php?action=login');
            exit();
        } else {
            $_SESSION['registro_error'] = "Error al registrar. El usuario ya podría existir.";
            header('Location: index.php?action=showRegister');
            exit();
        }
    }
}
