<?php

/**
 * ============================================
 * VISTA: LOGIN (Formulario de Inicio de Sesión)
 * ============================================
 * 
 * Esta vista muestra el formulario de login.
 * Incluye:
 * - Campo para ID de usuario
 * - Campo para contraseña
 * - Token CSRF oculto (seguridad)
 * - Mensajes de error/éxito
 * - Enlace para registro
 * 
 * La validación del lado cliente se realiza en validarDatos.js
 */

// ============================================
// VERIFICAR SI YA ESTÁ LOGUEADO
// ============================================
/**
 * Si el usuario ya tiene una sesión activa, lo redirigimos
 * directamente al dashboard. No tiene sentido mostrar el login.
 */
if (isset($_SESSION['usuario_logueado'])) {
    header("Location: index.php?action=dashboard");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Login MVC Seguro</title>

    <!-- ============================================
         META TAGS PARA SEO Y SEGURIDAD
         ============================================ -->
    <meta name="description" content="Sistema de login seguro con PHP MVC">
    <meta name="robots" content="noindex, nofollow">
    <!-- noindex, nofollow: No queremos que los buscadores indexen páginas de login -->

    <!-- Icono de la pagina -->
    <link rel="shortcut icon" href="https://iesplayamar.es/wp-content/uploads/2021/09/logo-ies-playamar.png" type="image/x-icon">

    <!-- ============================================
         LIBRERÍAS CSS EXTERNAS
         ============================================ -->
    <!-- Font Awesome - Iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <!-- Google Fonts - Tipografía Roboto -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet" />
    <!-- Bootstrap 5 CSS - Framework de estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="./Views/css/estilos.css">
</head>

<body>
    <!-- ============================================
         BOTÓN DE TEMA OSCURO/CLARO
         ============================================ -->
    <button id="btnOscuro" type="button" class="btn btn-primary rounded-circle m-3 p-2 mt-3">🌙</button>

    <section>
        <div class="container py-5">
            <div class="row d-flex align-items-center justify-content-center">

                <!-- ============================================
                     IMAGEN DECORATIVA
                     ============================================ -->
                <div class="col-md-8 col-lg-7 col-xl-6">
                    <img src="./Views/img/login.svg" class="img-fluid" alt="Imagen de login">
                </div>

                <!-- ============================================
                     FORMULARIO DE LOGIN
                     ============================================ -->
                <div class="col-md-7 col-lg-5 col-xl-5 offset-xl-1">

                    <!-- ============================================
                         MENSAJES DE FEEDBACK AL USUARIO
                         ============================================ -->
                    <?php
                    // Mensaje de sesión expirada (después de 2 horas)
                    if (isset($_GET['expired']) && $_GET['expired'] == 1) {
                        echo '<div class="alert alert-warning" role="alert">
                                <i class="bi bi-clock-history"></i> Tu sesión ha expirado. Por favor, inicia sesión de nuevo.
                              </div>';
                    }

                    // Mensaje de registro exitoso
                    if (isset($_SESSION['registro_exito'])) {
                        echo '<div class="alert alert-success" role="alert">
                                <i class="bi bi-check-circle"></i> ' . htmlspecialchars($_SESSION['registro_exito']) . '
                              </div>';
                        unset($_SESSION['registro_exito']);
                    }

                    // Mensaje de error (login fallido, CSRF inválido, etc.)
                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle"></i> ' . htmlspecialchars($_SESSION['error']) . '
                              </div>';
                        unset($_SESSION['error']);
                    }
                    ?>

                    <!-- ============================================
                         FORMULARIO
                         ============================================ -->
                    <!-- 
                        action: Envía a index.php?action=authenticate que ejecuta AuthController->authenticate()
                        method: POST para enviar datos de forma segura (no visibles en URL)
                        id: Para que JavaScript pueda añadir validación
                    -->
                    <form action="index.php?action=authenticate" method="POST" id="form1">

                        <!-- ============================================
                             CAMPO: ID DE USUARIO
                             ============================================ -->
                        <div class="form-floating mb-4">
                            <input type="text"
                                class="form-control"
                                id="idUser"
                                name="idUser"
                                placeholder="usuario@ejemplo.com"
                                autocomplete="username" />
                            <label for="idUser">Id Usuario (Email)</label>
                        </div>
                        <!-- Contenedor para errores de validación JS -->
                        <div id="idUserHelp" class="form-text text-danger mb-3">Errores aqui</div>

                        <!-- ============================================
                             CAMPO: CONTRASEÑA
                             ============================================ -->
                        <div class="form-floating mb-4">
                            <input type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Contraseña"
                                autocomplete="current-password" />
                            <label for="password">Contraseña</label>
                        </div>
                        <!-- Contenedor para errores de validación JS -->
                        <div id="passwordHelp" class="form-text text-danger mb-3">Errores aqui</div>

                        <!-- ============================================
                             TOKEN CSRF (SEGURIDAD)
                             ============================================ -->
                        <!-- 
                            Este campo oculto contiene el token CSRF generado en SessionConfig.php
                            El servidor verificará que este token coincida con el de la sesión
                            antes de procesar el login. Esto previene ataques CSRF.
                        -->
                        <input type="hidden"
                            name="csrf_token"
                            value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                        <!-- ============================================
                             BOTÓN DE ENVÍO
                             ============================================ -->
                        <div class="d-flex justify-content-around align-items-center mb-4">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                            </button>
                        </div>

                        <!-- ============================================
                             ENLACE A REGISTRO
                             ============================================ -->
                        <p class="text-center mt-3">
                            ¿No tienes cuenta? <a href="index.php?action=showRegister">Regístrate aquí</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         SCRIPTS JAVASCRIPT
         ============================================ -->
    <!-- Bootstrap 5 JS Bundle (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <!-- Script de validación del lado cliente -->
    <script src="./Views/js/validarDatos.js"></script>
</body>

</html>