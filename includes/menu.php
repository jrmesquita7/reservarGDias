<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lógica aprimorada para determinar o link da nova reserva
$linkNovaReserva = '/reservas/nova.php'; // Valor padrão

// 1. Verifica primeiro se há um parâmetro de origem na sessão
if (isset($_SESSION['origem_reserva'])) {
    if ($_SESSION['origem_reserva'] === 'laboratorios') {
        $linkNovaReserva = '/reservas/nova_reserva_lab.php';
    }
    unset($_SESSION['origem_reserva']); // Limpa após usar
} 
// 2. Se não houver sessão, verifica a página atual
else {
    $paginaAtual = basename($_SERVER['PHP_SELF']);
    if (strpos($paginaAtual, 'dashboard_laboratorios.php') !== false) {
        $linkNovaReserva = '/reservas/nova_reserva_lab.php';
    }
}

// Lógica aprimorada para determinar o link da listar reserva
$linkListarReserva = '/reservas/listar.php'; // Valor padrão

// 1. Verifica primeiro se há um parâmetro de origem na sessão
if (isset($_SESSION['origem_reserva'])) {
    if ($_SESSION['origem_reserva'] === 'laboratorios') {
        $linkListarReserva = '/reservas/listar.php.php';
    }
    unset($_SESSION['origem_reserva']); // Limpa após usar
} 
// 2. Se não houver sessão, verifica a página atual
else {
    $paginaAtual = basename($_SERVER['PHP_SELF']);
    if (strpos($paginaAtual, 'dashboard_laboratorios.php') !== false) {
        $linkListarReserva = '/reservas/listar_reservas_laboratorio.php';
    }
}
?>

?>
<style>
/* Estilos originais do menu - sem alterações */
.menu-toggle {
  display: none;
  font-size: 24px;
  cursor: pointer;
  color: #ecf0f1;
  background: none;
  border: none;
  padding: 0;
}

.header-nav {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

@media (max-width: 768px) {
  .menu-toggle {
    display: block;
  }
  .header-nav {
    display: none;
    flex-direction: column;
    background-color: #2c3e50;
    position: absolute;
    top: 60px;
    left: 0;
    right: 0;
    padding: 10px 20px;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    z-index: 1001;
  }
  .header-nav.active {
    display: flex;
  }
  .header-nav a {
    padding: 10px 0;
    border-bottom: 1px solid #34495e;
  }
  .header-nav a:last-child {
    border-bottom: none;
  }
}
</style>

<div class="header">
  <div style="display:flex; justify-content: space-between; align-items: center; width: 100%;">
    <div style="color: #ecf0f1; font-weight: bold; font-size: 20px;"></div>
    <button class="menu-toggle" aria-label="Toggle menu">&#9776;</button>
  </div>
  <nav class="header-nav">
    <a href="/dashboard.php">📋 Principal</a>
    <a href="<?= $linkNovaReserva ?>">➕ Nova reserva</a>
    <a href="<?= $linkListarReserva ?>">📋 Minhas reservas</a>
    <a href="/dashboard_laboratorios.php">🧪 Laboratórios</a>
    <a href="/usuarios/perfil.php"><i class="fa-solid fa-user"></i> Perfil</a>
    <a href="/auth/logout.php">🔄 Sair</a>
  </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.header-nav');
  toggle.addEventListener('click', function() {
    nav.classList.toggle('active');
  });
  
  // Fecha o menu ao clicar em um link (mobile)
  nav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth <= 768) {
        nav.classList.remove('active');
      }
    });
  });
});
</script>