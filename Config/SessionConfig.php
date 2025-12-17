<?php

/**
 * ============================================
 * CONFIGURACIÓN DE SESIÓN SEGURA
 * ============================================
 * 
 * Este archivo configura todos los parámetros de seguridad para las sesiones PHP.
 * Debe incluirse ANTES de cualquier session_start() en la aplicación.
 * 
 * Características de seguridad implementadas:
 * - Cookies seguras (HttpOnly, SameSite, Secure)
 * - Límite absoluto de sesión (2 horas)
 * - Regeneración periódica del ID de sesión (cada 20 minutos)
 * - Generación de token CSRF para protección contra ataques CSRF
 */

// ============================================
// 1. CONFIGURACIÓN DE PARÁMETROS DE COOKIE DE SESIÓN
// ============================================
/**
 * Configuramos las cookies de sesión con parámetros seguros antes de iniciar la sesión.
 * Esto DEBE hacerse ANTES de session_start()
 * 
 * Parámetros:
 * - lifetime: Tiempo de vida de la cookie en segundos (0 = hasta cerrar navegador, 3600 = 1 hora)
 * - path: Ruta donde la cookie es válida ('/' = todo el dominio)
 * - domain: Dominio donde la cookie es válida (vacío para localhost)
 * - secure: true = solo enviar por HTTPS (desactivar en desarrollo local)
 * - httponly: true = NO accesible desde JavaScript (previene robo por XSS)
 * - samesite: 'Strict' = cookie solo se envía en peticiones del mismo sitio (previene CSRF)
 */
session_set_cookie_params([
    'lifetime' => 3600,                     // Cookie expira en 1 hora (3600 segundos)
    'path' => '/',                          // Disponible en todo el dominio
    // 'domain' => 'tu-dominio.com',        // Descomentar y configurar en producción
    // 'secure' => true,                    // Descomentar cuando uses HTTPS en producción
    'httponly' => true,                     // NO accesible vía JavaScript (previene XSS)
    'samesite' => 'Strict',                 // Previene ataques CSRF
]);

// ============================================
// 2. INICIAR LA SESIÓN
// ============================================
/**
 * Iniciamos la sesión después de configurar los parámetros de la cookie.
 * session_start() crea una nueva sesión o resume una existente.
 */
session_start();

// ============================================
// 3. LÍMITE ABSOLUTO DE SESIÓN: 2 HORAS
// ============================================
/**
 * Establecemos un tiempo máximo absoluto para la sesión.
 * Después de 2 horas, la sesión se destruye automáticamente
 * independientemente de la actividad del usuario.
 * 
 * Esto es importante para:
 * - Limitar el tiempo de exposición si se roba una sesión
 * - Forzar re-autenticación periódica
 * - Cumplir con políticas de seguridad corporativas
 */
$session_max_lifetime = 7200;               // 2 horas = 7200 segundos

// Almacenar el timestamp de creación de la sesión si no existe
if (!isset($_SESSION['session_created'])) {
    $_SESSION['session_created'] = time();
}

// Verificar si la sesión ha superado el límite de 2 horas
if (time() - $_SESSION['session_created'] >= $session_max_lifetime) {
    // La sesión ha expirado, debemos destruirla de forma segura

    // 1. Limpiar todas las variables de sesión
    session_unset();

    // 2. Destruir la sesión
    session_destroy();

    // 3. Redirigir al login con mensaje de sesión expirada
    header("Location: index.php?action=login&expired=1");
    exit();
}

// ============================================
// 4. REGENERACIÓN PERIÓDICA DEL ID DE SESIÓN
// ============================================
/**
 * Regeneramos el ID de sesión cada 20 minutos.
 * Esto dificulta los ataques de fijación de sesión (session fixation)
 * donde un atacante intenta forzar un ID de sesión conocido.
 * 
 * session_regenerate_id(true):
 * - Genera un nuevo ID de sesión aleatorio
 * - El parámetro 'true' elimina el archivo de sesión antiguo
 * - Mantiene todos los datos de la sesión
 */
$regenerate_interval = 1200;                // 20 minutos = 1200 segundos

// Almacenar el tiempo de la última regeneración si no existe
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
}

// Verificar si es necesario regenerar el ID
if (time() - $_SESSION['last_regeneration'] >= $regenerate_interval) {
    // Regenerar el ID de sesión y eliminar la sesión antigua
    session_regenerate_id(true);

    // Actualizar el timestamp para el próximo intervalo
    $_SESSION['last_regeneration'] = time();
}

// ============================================
// 5. GENERACIÓN DE TOKEN CSRF
// ============================================
/**
 * Token CSRF (Cross-Site Request Forgery):
 * 
 * Este token se genera una vez por sesión y se incluye en todos los formularios.
 * Cuando el servidor recibe un formulario, verifica que el token coincida
 * con el almacenado en la sesión.
 * 
 * Esto previene ataques donde un sitio malicioso intenta enviar
 * formularios en nombre del usuario autenticado.
 * 
 * Proceso:
 * 1. Se genera un token aleatorio de 64 bytes
 * 2. Se codifica en hexadecimal para poder usarlo en HTML
 * 3. Se almacena en $_SESSION['csrf_token']
 * 4. Se incluye como campo oculto en los formularios
 * 5. Se verifica en el servidor antes de procesar operaciones sensibles
 */
if (empty($_SESSION['csrf_token'])) {
    // Generar 64 bytes aleatorios criptográficamente seguros
    $random_bytes = openssl_random_pseudo_bytes(64);

    // Convertir a hexadecimal para uso en formularios HTML
    $csrf_token = bin2hex($random_bytes);

    // Almacenar el token en la sesión
    $_SESSION['csrf_token'] = $csrf_token;
}

// ============================================
// FIN DE LA CONFIGURACIÓN DE SESIÓN
// ============================================
/**
 * Después de este punto, la sesión está:
 * - Configurada con cookies seguras
 * - Protegida con límite de tiempo de 2 horas
 * - Con ID que se regenera cada 20 minutos
 * - Con token CSRF disponible para formularios
 */
