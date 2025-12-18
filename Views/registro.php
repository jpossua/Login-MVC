<?php

/**
 * ============================================
 * VISTA: REGISTRO (Formulario de Registro de Usuario)
 * ============================================
 * FUNCIONALIDAD OPCIONAL - Puntos 9 y 10 del ejercicio
 * ============================================
 * 
 * Esta vista muestra el formulario de registro de nuevos usuarios.
 * Incluye:
 * - Campo para ID de usuario (email)
 * - Campo para contraseña
 * - Campo para nombre
 * - Campo para apellidos
 * - Token CSRF oculto (seguridad)
 * - Información sobre aprobación de admin (Punto 10 - OPCIONAL)
 * 
 * Los usuarios nuevos se crean con admitido=0 (pendiente de aprobación)
 */

// ============================================
// VERIFICAR SI YA ESTÁ LOGUEADO
// ============================================
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
    <title>Registro de Usuario - Login MVC Seguro</title>

    <!-- Meta tags -->
    <meta name="description" content="Registro de nuevos usuarios">
    <meta name="robots" content="noindex, nofollow">

    <!-- Icono de la pagina -->
    <link rel="shortcut icon" href="https://iesplayamar.es/wp-content/uploads/2021/09/logo-ies-playamar.png" type="image/x-icon">

    <!-- ============================================
         LIBRERÍAS CSS EXTERNAS
         ============================================ -->
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet" />
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="./Views/css/estilos.css">
</head>

<body>

    <!-- Botón de tema oscuro/claro -->
    <button id="btnOscuro" type="button" class="btn btn-primary rounded-circle m-3 p-2 mt-3">🌙</button>

    <section>
        <div class="container py-5">
            <div class="row d-flex align-items-center justify-content-center">

                <!-- Imagen decorativa -->
                <div class="col-md-8 col-lg-7 col-xl-6">
                    <img src="./Views/img/login.svg" class="img-fluid" alt="Imagen de registro">
                </div>

                <!-- Formulario de registro -->
                <div class="col-md-7 col-lg-5 col-xl-5 offset-xl-1">

                    <!-- ============================================
                         TÍTULO DEL FORMULARIO
                         ============================================ -->
                    <h2 class="mb-4"><i class="bi bi-person-plus-fill"></i> Registro de Usuario</h2>

                    <!-- ============================================
                         MENSAJES DE ERROR
                         ============================================ -->
                    <?php
                    if (isset($_SESSION['registro_error'])) {
                        echo '<div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle"></i> ' . htmlspecialchars($_SESSION['registro_error']) . '
                              </div>';
                        unset($_SESSION['registro_error']);
                    }
                    ?>

                    <!-- ============================================
                         INFORMACIÓN SOBRE APROBACIÓN
                         ============================================ -->
                    <!-- 
                        Los nuevos usuarios se crean con admitido=0.
                        Un administrador debe cambiar este valor a 1
                        para que el usuario pueda iniciar sesión.
                    -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Tu cuenta estará <strong>pendiente de aprobación</strong> por el administrador.
                    </div>

                    <!-- ============================================
                         FORMULARIO DE REGISTRO
                         ============================================ -->
                    <form action="index.php?action=register" method="POST" id="formRegistro">

                        <!-- ============================================
                             CAMPO: ID DE USUARIO
                             ============================================ -->
                        <div class="form-floating mb-4">
                            <input type="text"
                                class="form-control"
                                id="idUser"
                                name="idUser"
                                placeholder="correo@ejemplo.com"
                                autocomplete="username" />
                            <label for="idUser">Id Usuario (Email) - 8-15 caracteres</label>
                        </div>
                        <div id="idUserHelp" class="form-text text-danger mb-3">Errores aqui</div>

                        <!-- ============================================
                             CAMPO: CONTRASEÑA
                             ============================================ -->
                        <!-- 
                            Requisitos de contraseña (validados en JS y PHP):
                            - 8-15 caracteres
                            - Al menos una mayúscula
                            - Al menos una minúscula
                            - Al menos un número
                            - Al menos un carácter especial: !@#$%^&*_+-[]{}:,.?
                            - NO contener: ' " \ / < > = ( )
                        -->
                        <div class="form-floating mb-4">
                            <input type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Contraseña"
                                autocomplete="new-password" />
                            <label for="password">Contraseña (8-15 caracteres, mayús, minús, número, especial)</label>
                        </div>
                        <div id="passwordHelp" class="form-text text-danger mb-3">Errores aqui</div>

                        <!-- ============================================
                             CAMPO: NOMBRE
                             ============================================ -->
                        <div class="form-floating mb-4">
                            <input type="text"
                                class="form-control"
                                id="nombre"
                                name="nombre"
                                placeholder="Tu nombre"
                                autocomplete="given-name" />
                            <label for="nombre">Nombre</label>
                        </div>
                        <div id="nombreHelp" class="form-text text-danger mb-3">Errores aqui</div>

                        <!-- ============================================
                             CAMPO: APELLIDOS
                             ============================================ -->
                        <div class="form-floating mb-4">
                            <input type="text"
                                class="form-control"
                                id="apellidos"
                                name="apellidos"
                                placeholder="Tus apellidos"
                                autocomplete="family-name" />
                            <label for="apellidos">Apellidos</label>
                        </div>
                        <div id="apellidosHelp" class="form-text text-danger mb-3">Errores aqui</div>

                        <!-- ============================================
                             TOKEN CSRF (SEGURIDAD)
                             ============================================ -->
                        <input type="hidden"
                            name="csrf_token"
                            value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                        <!-- ============================================
                             BOTÓN DE ENVÍO
                             ============================================ -->
                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            <i class="bi bi-person-plus"></i> Registrarse
                        </button>

                        <!-- ============================================
                             ENLACE A LOGIN
                             ============================================ -->
                        <p class="text-center mt-3">
                            ¿Ya tienes cuenta? <a href="index.php?action=login">Iniciar Sesión</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <!-- Script de validación -->
    <script src="./Views/js/validarDatos.js"></script>
</body>

</html>