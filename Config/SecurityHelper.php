<?php

/**
 * ============================================
 * HELPER DE SEGURIDAD
 * ============================================
 * 
 * Clase estática con funciones de utilidad para seguridad.
 * Proporciona métodos para:
 * - Sanitización de datos de entrada
 * - Validación de tokens CSRF
 * - Control de intentos de login
 */

class SecurityHelper
{
    // ============================================
    // CONSTANTES DE CONFIGURACIÓN
    // ============================================

    /**
     * Número máximo de intentos de login permitidos
     * Después de esto, el usuario queda bloqueado temporalmente
     */
    const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * Tiempo de bloqueo después de exceder intentos (en segundos)
     * 15 minutos = 900 segundos
     */
    const LOCKOUT_TIME = 900;

    // ============================================
    // SANITIZACIÓN DE DATOS
    // ============================================

    /**
     * Sanitiza un string de entrada para prevenir XSS e inyección
     * 
     * Proceso de sanitización:
     * 1. trim() - Elimina espacios en blanco al inicio y final
     * 2. stripslashes() - Elimina barras invertidas (anti-escape)
     * 3. htmlspecialchars() - Convierte caracteres especiales a entidades HTML
     *    - ENT_QUOTES: Convierte comillas simples y dobles
     *    - UTF-8: Codificación de caracteres
     * 
     * Esto previene:
     * - Ataques XSS (Cross-Site Scripting)
     * - Inyección de código HTML/JavaScript
     * 
     * @param string $data Dato a sanitizar
     * @return string Dato sanitizado y seguro
     */
    public static function sanitizeInput($data)
    {
        // 1. Eliminar espacios en blanco al inicio y final
        $data = trim($data);

        // 2. Eliminar barras invertidas añadidas por magic_quotes (PHP antiguo)
        $data = stripslashes($data);

        // 3. Convertir caracteres especiales a entidades HTML
        //    Esto previene que código malicioso sea ejecutado
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

        return $data;
    }

    // ============================================
    // VALIDACIÓN DE TOKEN CSRF
    // ============================================

    /**
     * Verifica que el token CSRF del formulario coincida con el de la sesión
     * 
     * Esta verificación debe hacerse ANTES de procesar cualquier operación
     * sensible como login, registro, modificación de datos, etc.
     * 
     * El ataque CSRF funciona así:
     * 1. Usuario está autenticado en tu sitio
     * 2. Usuario visita sitio malicioso
     * 3. Sitio malicioso envía formulario a tu sitio
     * 4. Sin token CSRF, tu sitio procesaría la petición
     * 
     * Con token CSRF:
     * - El sitio malicioso NO conoce el token (es aleatorio y por sesión)
     * - La petición se rechaza si el token no coincide
     * 
     * @return bool true si el token es válido, false en caso contrario
     */
    public static function validateCSRFToken()
    {
        // Verificar que existan ambos tokens (POST y sesión)
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            return false;
        }

        // Comparar tokens de forma segura (timing-safe comparison)
        // hash_equals previene ataques de timing donde se mide el tiempo de comparación
        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }

    // ============================================
    // CONTROL DE INTENTOS DE LOGIN (Punto 8 del ejercicio)
    // ============================================
    // Esta funcionalidad implementa el control de límite de intentos de acceso
    // para prevenir ataques de fuerza bruta

    /**
     * Verifica si el usuario está bloqueado por exceso de intentos fallidos
     * 
     * Funcionamiento:
     * 1. Inicializa contador si no existe
     * 2. Si ha excedido MAX_LOGIN_ATTEMPTS, verifica tiempo de bloqueo
     * 3. Si aún está en periodo de bloqueo, retorna mensaje de error
     * 4. Si el tiempo de bloqueo pasó, resetea el contador
     * 
     * @return array ['blocked' => bool, 'message' => string, 'remaining_minutes' => int]
     */
    public static function checkLoginAttempts()
    {
        // Inicializar el contador de intentos si no existe
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['first_attempt_time'] = time();
        }

        // Verificar si el usuario ha excedido el límite de intentos
        if ($_SESSION['login_attempts'] >= self::MAX_LOGIN_ATTEMPTS) {
            // Calcular tiempo transcurrido desde el primer intento
            $time_passed = time() - $_SESSION['first_attempt_time'];

            if ($time_passed < self::LOCKOUT_TIME) {
                // Aún está dentro del periodo de bloqueo
                $remaining_minutes = ceil((self::LOCKOUT_TIME - $time_passed) / 60);

                return [
                    'blocked' => true,
                    'message' => "Demasiados intentos fallidos. Espera {$remaining_minutes} minutos antes de volver a intentarlo.",
                    'remaining_minutes' => $remaining_minutes
                ];
            } else {
                // El tiempo de bloqueo ha pasado, reiniciar contador
                self::resetLoginAttempts();
            }
        }

        return [
            'blocked' => false,
            'message' => '',
            'remaining_minutes' => 0
        ];
    }

    /**
     * Incrementa el contador de intentos fallidos de login
     * 
     * Se llama cada vez que un intento de login falla.
     * Mantiene registro del primer intento para calcular el tiempo de bloqueo.
     */
    public static function incrementLoginAttempts()
    {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['first_attempt_time'] = time();
        }

        $_SESSION['login_attempts']++;
    }

    /**
     * Resetea el contador de intentos de login
     * 
     * Se llama cuando:
     * - El usuario hace login exitoso
     * - El tiempo de bloqueo ha expirado
     */
    public static function resetLoginAttempts()
    {
        $_SESSION['login_attempts'] = 0;
        unset($_SESSION['first_attempt_time']);
    }

    // ============================================
    // VALIDACIÓN DE CONTRASEÑA
    // ============================================

    /**
     * Valida que una contraseña cumpla los requisitos de seguridad
     * 
     * Requisitos:
     * - Longitud entre 8 y 15 caracteres
     * - Al menos una letra mayúscula
     * - Al menos una letra minúscula
     * - Al menos un número
     * - Al menos un carácter especial permitido
     * - NO contener caracteres peligrosos: ' " \ / < > = ( )
     * 
     * @param string $password Contraseña a validar
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validatePassword($password)
    {
        $errors = [];

        // Verificar longitud (8-15 caracteres)
        if (strlen($password) < 8 || strlen($password) > 15) {
            $errors[] = "La contraseña debe tener entre 8 y 15 caracteres";
        }

        // Verificar que no contenga caracteres peligrosos
        if (preg_match('/[\'\"\\\\\/\<\>=\(\)]/', $password)) {
            $errors[] = "La contraseña no puede contener: ' \" \\ / < > = ( )";
        }

        // Verificar al menos una mayúscula
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una mayúscula";
        }

        // Verificar al menos una minúscula
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una minúscula";
        }

        // Verificar al menos un número
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un número";
        }

        // Verificar al menos un carácter especial permitido
        if (!preg_match('/[!@#$%^&*_+=\-\[\]{};:,.?]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un carácter especial: !@#\$%^&*_+-[]{}:,.?";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    // ============================================
    // VALIDACIÓN DE ID DE USUARIO
    // ============================================

    /**
     * Valida que el ID de usuario cumpla los requisitos
     * 
     * Requisitos:
     * - Longitud entre 8 y 15 caracteres
     * 
     * @param string $idUser ID de usuario a validar
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validateIdUser($idUser)
    {
        $errors = [];

        // Verificar longitud (8-15 caracteres)
        if (strlen($idUser) < 8 || strlen($idUser) > 15) {
            $errors[] = "El ID de usuario debe tener entre 8 y 15 caracteres";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
