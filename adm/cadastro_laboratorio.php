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
    $descricao = $_POST['descricao'];
    $status = 'Disponível';

    try {
        $stmt = $pdo->prepare("INSERT INTO laboratorios (nome, descricao, status) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $descricao, $status]);
        $mensagem = "✅ Laboratório cadastrado com sucesso!";
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
    <h2>Cadastrar Novo Laboratório/Sala</h2>

    <?php if ($mensagem): ?>
        <div class="alert"> <?= $mensagem ?> </div>
    <?php endif; ?>

    <form method="POST">
        <label for="nome">Nome do Laboratório/sala:</label>
        <input type="text" name="nome" required>

        <label for="descricao">Descrição:</label>
        <textarea name="descricao" rows="4" required></textarea>

        <button type="submit">Cadastrar</button>
    </form>

    <a href="usuarios.php" class="back-link">⬅ Voltar</a>
</div>
</body>
</html>
