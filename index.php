<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="RestaurantPOS - Sistema de punto de venta completo para restaurantes con gestión de mesas, delivery, facturación, caja y reportes.">
  <title>RestaurantPOS - Sistema POS para Restaurantes</title>

  <!-- Fuentes e Iconos -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <style>
    :root {
      --primary: #4a6bff;
      --primary-dark: #3a56cc;
      --secondary: #ff6b6b;
      --light-bg: #f8f9fa;
      --dark-bg: #1f2937;
      --text-light: #ffffff;
      --text-dark: #1f2937;
      --card-light: #ffffff;
      --card-dark: #2d3748;
      --transition: all 0.3s ease;
    }

    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      background: var(--light-bg);
      color: var(--text-dark);
      transition: var(--transition);
    }

    body.dark-mode {
      background: var(--dark-bg);
      color: var(--text-light);
    }

    .container {
      max-width: 1200px;
      margin: auto;
      padding: 2rem;
      text-align: center;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    .logo {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary);
    }

    .mode-toggle {
      cursor: pointer;
      border: none;
      background: none;
      font-size: 1.8rem;
      color: var(--primary);
      transition: var(--transition);
    }

    h1 {
      font-size: 3rem;
      font-weight: 700;
      background: linear-gradient(45deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      color: transparent;
      margin-bottom: 1rem;
    }

    p.subtitle {
      font-size: 1.2rem;
      margin-bottom: 2.5rem;
      max-width: 800px;
      margin-left: auto;
      margin-right: auto;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: #fff;
      text-decoration: none;
      padding: 1rem 2rem;
      border-radius: 50px;
      font-weight: 600;
      transition: var(--transition);
    }

    .btn:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 25px rgba(74,107,255,0.3);
    }

    .features {
      margin-top: 3.5rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
    }

    .card {
      background: var(--card-light);
      padding: 2rem;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.07);
      transition: var(--transition);
    }

    body.dark-mode .card {
      background: var(--card-dark);
      box-shadow: 0 8px 20px rgba(0,0,0,0.4);
    }

    .card:hover {
      transform: translateY(-6px);
    }

    .icon {
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 1rem;
    }

    h3 {
      margin-bottom: 0.5rem;
      font-size: 1.25rem;
    }

    footer {
      text-align: center;
      padding: 1.5rem;
      margin-top: 3rem;
      background: var(--dark-bg);
      color: #fff;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <div class="logo">RestaurantPOS</div>
      <button class="mode-toggle" onclick="toggleMode()" title="Cambiar modo">
        <span class="material-icons">brightness_6</span>
      </button>
    </header>

    <h1>GESTIÓN COMPLETA DE RESTAURANTES</h1>
    <p class="subtitle">RestaurantPOS es un sistema de punto de venta moderno y escalable diseñado para restaurantes que buscan eficiencia, control y seguridad. Administra mesas, pedidos, delivery, inventario, facturación y caja en una sola plataforma.</p>

    <a href="Restro/admin/" class="btn">
      <span class="material-icons">login</span> Iniciar Sesión
    </a>

    <div class="features">
      <div class="card">
        <div class="icon"><span class="material-icons">speed</span></div>
        <h3>Rendimiento</h3>
        <p>Procesa pedidos en segundos y mantén tus operaciones siempre ágiles.</p>
      </div>
      <div class="card">
        <div class="icon"><span class="material-icons">insights</span></div>
        <h3>Reportes</h3>
        <p>Accede a reportes detallados de ventas, caja y rendimiento en tiempo real.</p>
      </div>
      <div class="card">
        <div class="icon"><span class="material-icons">security</span></div>
        <h3>Seguridad</h3>
        <p>Tus datos están protegidos con autenticación por roles, auditoría y cifrado.</p>
      </div>
      <div class="card">
        <div class="icon"><span class="material-icons">table_restaurant</span></div>
        <h3>Gestión de Mesas</h3>
        <p>Controla disponibilidad, asigna pedidos y optimiza la atención en mesas.</p>
      </div>
      <div class="card">
        <div class="icon"><span class="material-icons">delivery_dining</span></div>
        <h3>Delivery Integrado</h3>
        <p>Administra pedidos para llevar con seguimiento en cada etapa de entrega.</p>
      </div>
    </div>
  </div>

  <footer>
    &copy; 2025 RestaurantPOS. Desarrollado por Pedro Ureña. Todos los derechos reservados.
  </footer>

  <script>
    function toggleMode() {
      document.body.classList.toggle("dark-mode");
    }
  </script>
</body>
</html>
