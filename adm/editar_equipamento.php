<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/db.php';

// Verifica se o usuário logado é do grupo administrador
$stmt = $pdo->prepare("SELECT id_grupo FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario_logado = $stmt->fetch();

if (!$usuario_logado || $usuario_logado['id_grupo'] != 1) {
    header('Location: ../menu.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$mensagem = '';

// Buscar dados do item
$stmt = $pdo->prepare("SELECT * FROM itens WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    die("Item não encontrado.");
}

// Atualizar nome e status do item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE itens SET nome = ?, status = ? WHERE id = ?");
    if ($stmt->execute([$nome, $status, $id])) {
        $mensagem = "✅ Nome e status atualizados com sucesso.";
        $item['nome'] = $nome;
        $item['status'] = $status;
    } else {
        $mensagem = "❌ Erro ao atualizar item.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Item</title>
    <link rel="stylesheet" href="../assets/forms.css">
</head>
<body>
    <div class="container">
        <h2>Editar Item (Equipamento)</h2>

        <?php if ($mensagem): ?>
            <div class="alert"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($item['nome']) ?>" required>

            <label for="status">Status:</label>
            <select name="status" id="status" required>
                <option value="Disponível" <?= $item['status'] === 'Disponível' ? 'selected' : '' ?>>Disponível</option>
                <option value="Em manutenção" <?= $item['status'] === 'Em manutenção' ? 'selected' : '' ?>>Em manutenção</option>
            </select>

            <button type="submit">Salvar</button>
        </form>

        <a href="usuarios.php" class="back-link">⬅ Voltar</a>
    </div>
</body>
</html>
