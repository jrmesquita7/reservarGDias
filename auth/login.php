<?php
session_start();
require_once '../config/db.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // Verificação simples (sem hash) — APENAS PARA TESTES
    if ($usuario && $senha === $usuario['senha']) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        header('Location: ../menu.php');
        exit;
    } else {
        $erro = 'Usuário ou senha inválidos.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<head>
    <meta charset="UTF-8">
    <title>Login - Reservas</title>
    <link rel="stylesheet" href="../assets/login.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php if ($erro): ?>
            <div class="alert"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" required>

            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
