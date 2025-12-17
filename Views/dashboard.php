<?php

/**
 * ============================================
 * VISTA: DASHBOARD (Página de Bienvenida)
 * ============================================
 * 
 * Esta vista es la página protegida que solo pueden ver
 * los usuarios autenticados.
 * 
 * Muestra:
 * - Mensaje de bienvenida con nombre del usuario
 * - Información sobre la seguridad de la sesión
 * - Botón para cerrar sesión
 * 
 * IMPORTANTE: Esta página solo se muestra si el usuario
 * tiene una sesión válida. El controlador verifica esto
 * antes de incluir esta vista.
 */

// La sesión ya está iniciada en index.php => SessionConfig.php
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Login MVC Seguro</title>

    <!-- Meta tags -->
    <meta name="description" content="Panel de usuario">
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
                    <img src="./Views/img/login.svg" class="img-fluid" alt="Imagen de bienvenida">
                </div>

                <!-- Contenido del dashboard -->
                <div class="col-md-7 col-lg-5 col-xl-5 offset-xl-1">

                    <!-- ============================================
                         TÍTULO DE BIENVENIDA
                         ============================================ -->
                    <h2 class="mb-4">
                        <i class="bi bi-hand-thumbs-up-fill text-success"></i> ¡Bienvenido!
                    </h2>

                    <!-- ============================================
                         INFORMACIÓN DEL USUARIO
                         ============================================ -->
                    <!-- 
                        Mostramos el nombre del usuario almacenado en la sesión.
                        Usamos htmlspecialchars() para prevenir XSS incluso
                        con datos de nuestra propia base de datos.
                    -->
                    <div class="alert alert-success">
                        <i class="bi bi-person-circle"></i>
                        <strong>Usuario:</strong>
                        <?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellidos']); ?>
                    </div>

                    <!-- ============================================
                         CARD DE INFORMACIÓN DE SESIÓN
                         ============================================ -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-shield-check text-primary"></i> Sesión Activa
                            </h5>
                            <p class="card-text">
                                Has iniciado sesión correctamente. Esta es la página de inicio protegida.
                            </p>
                            <p class="card-text text-muted">
                                <small>Si intentas entrar aquí sin loguearte, serás redirigido al login.</small>
                            </p>
                        </div>
                    </div>


                    <!-- ============================================
                         BOTÓN DE CERRAR SESIÓN
                         ============================================ -->
                    <!-- 
                        El logout se realiza de forma segura:
                        1. Limpia variables de sesión
                        2. Destruye la sesión
                        3. Elimina la cookie de sesión
                        4. Redirige al login
                    -->
                    <a href="./index.php?action=logout" class="btn btn-danger btn-lg btn-block">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </a>

                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <!-- Script para el botón de tema -->
    <script src="./Views/js/validarDatos.js"></script>
</body>

</html>