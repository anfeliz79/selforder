<?php
session_start();
if (isset($_SESSION['user'])) {
  header("Location: /admin/dashboard.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - SelfOrder</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      background-color: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }
    .login-card {
      max-width: 900px;
      width: 100%;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    }
    .login-left {
      background: #fff;
      padding: 50px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .login-right {
      background: url('/images/login-bg.jpg') no-repeat center center;
      background-size: cover;
    }
    .form-control {
      border-radius: 30px;
      padding: 12px 20px;
    }
    .btn-login {
      border-radius: 30px;
      padding: 12px;
    }
    .logo {
      max-height: 50px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
<div class="login-card row g-0 bg-white">
  <!-- Lado Izquierdo -->
  <div class="col-md-6 login-left">
    <div class="text-center">
      <img src="/images/logo.png" alt="Logo" class="logo">
    </div>
    <h4 class="mb-4 text-center">Bienvenido 👋</h4>
    <form id="loginForm">
      <div class="mb-3">
        <input type="text" id="username" class="form-control" placeholder="Usuario" required>
      </div>
      <div class="mb-3">
        <input type="password" id="password" class="form-control" placeholder="Contraseña" required>
      </div>
      <button type="submit" class="btn btn-primary w-100 btn-login">Login</button>
    </form>
  </div>
  <!-- Lado Derecho -->
  <div class="col-md-6 login-right d-none d-md-block"></div>
</div>

<script>
document.getElementById("loginForm").addEventListener("submit", function(e){
  e.preventDefault();
  fetch("/auth.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({
      username: document.getElementById("username").value,
      password: document.getElementById("password").value
    })
  })
  .then(r=>r.json())
  .then(res=>{
    if(res.success){
      window.location.href="/admin/dashboard.php";
    } else {
      Swal.fire("Error", res.message, "error");
    }
  });
});
</script>
</body>
</html>
