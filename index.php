<?php

/**
 * ============================================
 * INDEX.PHP - PUNTO DE ENTRADA DE LA APLICACIÓN
 * ============================================
 * 
 * Este archivo es el Front Controller del patrón MVC.
 * Todas las peticiones pasan por aquí y se enrutan al controlador apropiado.
 * 
 * Flujo de la aplicación:
 * 1. Se carga la configuración de sesión segura
 * 2. Se cargan el controlador y el modelo
 * 3. Se determina la acción a ejecutar según el parámetro 'action'
 * 4. Se ejecuta el método correspondiente del controlador
 */

// ============================================
// 1. CARGAR CONFIGURACIÓN DE SEGURIDAD
// ============================================
/**
 * Incluimos la configuración de sesión segura.
 * Esto DEBE ser lo primero, antes de cualquier session_start().
 * 
 * SessionConfig.php configura:
 * - Cookies seguras (HttpOnly, SameSite)
 * - Límite de sesión de 2 horas
 * - Regeneración de ID cada 20 minutos
 * - Token CSRF
 */
require_once 'Config/SessionConfig.php';

// ============================================
// 2. CARGAR CONTROLADORES Y MODELOS
// ============================================
/**
 * Cargamos las clases necesarias:
 * - AuthController: Controlador de autenticación (login, logout, registro)
 * - Usuario: Modelo para operaciones con la base de datos
 * - SecurityHelper: Funciones de seguridad (sanitización, CSRF, etc.)
 */
require_once 'Controllers/AuthController.php';  // Controlador de autenticación
require_once 'Models/User.php';                 // Modelo de usuarios
require_once 'Config/SecurityHelper.php';       // Ayudante de seguridad

// ============================================
// 3. CREAR INSTANCIA DEL CONTROLADOR
// ============================================
/**
 * Creamos una instancia del controlador de autenticación.
 * El constructor del controlador inicializa el modelo de usuario
 * que a su vez establece la conexión con la base de datos.
 */
$controller = new AuthController();

// ============================================
// 4. ENRUTAMIENTO BASADO EN EL PARÁMETRO 'action'
// ============================================
/**
 * Simple sistema de enrutamiento.
 * Leemos el parámetro 'action' de la URL y ejecutamos el método correspondiente.
 * 
 * Rutas disponibles:
 * - (sin action)        -> Muestra formulario de login
 * - ?action=login       -> Muestra formulario de login
 * - ?action=authenticate -> Procesa el login
 * - ?action=dashboard   -> Página de bienvenida (requiere autenticación)
 * - ?action=logout      -> Cierra la sesión
 * - ?action=showRegister -> Muestra formulario de registro (OPCIONAL - Punto 9)
 * - ?action=register    -> Procesa el registro (OPCIONAL - Puntos 9 y 10)
 */
if (!isset($_REQUEST['action'])) {
    // Primera visita: mostrar formulario de login
    $controller->login();
} else {
    // Enrutar según la acción solicitada
    switch ($_REQUEST['action']) {

        case 'login':
            // Mostrar formulario de login
            $controller->login();
            break;

        case 'authenticate':
            // Procesar autenticación (verifica credenciales)
            $controller->authenticate();
            break;

        case 'dashboard':
            // Mostrar página de bienvenida (solo usuarios autenticados)
            $controller->dashboard();
            break;

        case 'logout':
            // Cerrar sesión de forma segura
            $controller->logout();
            break;

        case 'showRegister':
            // OPCIONAL - Punto 9: Mostrar formulario de registro
            $controller->showRegister();
            break;

        case 'register':
            // OPCIONAL - Puntos 9 y 10: Procesar registro de nuevo usuario
            $controller->register();
            break;

        default:
            // Acción no reconocida: mostrar login
            $controller->login();
            break;
    }
}
