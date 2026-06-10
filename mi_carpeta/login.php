<?php
include ("models/conexion_bd.php");
include ("controlador.php");
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="stylesheet" href="css/estilos.css" />
    <link rel="icon" type="image/png" href="imagenes/fundasalud.png" />
    <script
      src="https://kit.fontawesome.com/9f06ad9705.js"
      crossorigin="anonymous"
    ></script>
  </head>
  <body>
    <div class="envoltorio-principal">
      <div class="logo-container">
        <img
          src="imagenes/fundasalud.png"
          alt="Fundasalud Trujillo"
          class="logo"
        />
      </div>
      <div class="container">
        <h1>Iniciar Sesión</h1>
        <div class="container_text1"></div>
        <form action="#" method="POST">
          <label class="form__group form__div" for="usuario">
            <span><i class="fa-solid fa-user"></i></span>
            <input
              class="campo_usuario"
              type="text"
              id="usuario"
              name="usuario"
              
              autocomplete="username"
              placeholder="Usuario"
            />
          </label>
          <label class="form__group form__div" for="contrasena">
            <span><i class="fa-solid fa-lock"></i></span>
            <input
              class="campo_pass"
              type="password"
              id="contrasena"
              name="contrasena"
              
              autocomplete="current-password"
              placeholder="Ingresa tu contraseña"
            />
          </label>
          <div class="form__boton-lgin">
            <button name="btningresar" class="form__boton-login" type="submit">
              <i class="fa-solid fa-right-to-bracket"></i>
              Iniciar Sesión
            </button>
          </div>
        </form>
      </div>
    </div>
    <script src="js/main.js"></script>
  </body>
</html>
