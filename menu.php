<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth/login.php');
    exit;
}

require_once 'config/db.php';

// Buscar o grupo do usuário
$stmt = $pdo->prepare("SELECT id_grupo FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();
$grupo = $usuario['id_grupo'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu de Reservas</title>
    <link rel="stylesheet" href="assets/login.css">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-hover: #2980b9;
            --bg-color: #f5f7fa;
            --text-color: #2c3e50;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, sans-serif;
            background-color: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .menu-container {
            width: 100%;
            max-width: 400px;
        }

        .menu-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 30px 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .menu-card h2 {
            color: var(--text-color);
            margin-bottom: 25px;
            font-size: 1.5rem;
        }

        .menu-card a {
            display: block;
            padding: 14px;
            margin-bottom: 16px;
            background-color: var(--primary-color);
            color: #fff;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .menu-card a:hover {
            background-color: var(--primary-hover);
        }

        .logout {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #888;
        }

        .logout a {
            color: #c0392b;
            text-decoration: none;
        }

        .logout a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .menu-card {
                padding: 20px;
            }

            .menu-card h2 {
                font-size: 1.3rem;
            }

            .menu-card a {
                padding: 12px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <div class="menu-container">
        <div class="menu-card">
            <h2>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h2>
            <a href="dashboard.php">📺 Reservar Equipamentos</a>
            <a href="dashboard_laboratorios.php">🧪 Reservar Laboratórios</a>
            <?php if ($grupo == 1): ?>
                <a href="adm/usuarios.php">⚙️ Painel Administrativo</a>
            <?php endif; ?>
            <div class="logout">
                <a href="auth/logout.php">Sair</a>
            </div>
        </div>
    </div>
</body>
</html>
