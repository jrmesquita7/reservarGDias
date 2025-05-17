<?php
session_start();
require_once '../config/db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Verifica se o usuário logado é do grupo Administrador
$stmt = $pdo->prepare("SELECT id_grupo FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

if (!$usuario || $usuario['id_grupo'] != 1) {
    header('Location: ../menu.php');
    exit;
}

// Processar formulário
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $tipo = 'Laboratório';
    $localizacao = '';
    $status = 'Disponível';

    try {
        $stmt = $pdo->prepare("INSERT INTO itens (nome, tipo, localizacao, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $tipo, $localizacao, $status]);
        $mensagem = "✅ Equipamento cadastrado com sucesso!";
    } catch (PDOException $e) {
        $mensagem = "❌ Erro ao cadastrar: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Laboratório</title>
    <link rel="stylesheet" href="../assets/forms.css">
</head>
<body>
<div class="container">
    <h2>Cadastrar Novo Equipamento</h2>

    <?php if ($mensagem): ?>
        <div class="alert"> <?= $mensagem ?> </div>
    <?php endif; ?>

    <form method="POST">
        <label for="nome">Nome do Equipamento:</label>
        <input type="text" name="nome" required>


        <button type="submit">Cadastrar</button>
    </form>

    <a href="usuarios.php" class="back-link">⬅ Voltar</a>
</div>
</body>
</html>
