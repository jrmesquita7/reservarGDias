<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../config/db.php';

// Listar todos os usuários
$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Administração de Usuários</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .acoes a {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Usuários do Sistema</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Grupo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= $usuario['id'] ?></td>
                        <td><?= htmlspecialchars($usuario['nome']) ?></td>
                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                        <td><?= $usuario['grupo'] ?></td>
                        <td class="acoes">
                            <a href="editar_usuario.php?id=<?= $usuario['id'] ?>">Editar</a>
                            <a href="excluir_usuario.php?id=<?= $usuario['id'] ?>" onclick="return confirm('Deseja realmente excluir este usuário?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <br>
        <a href="dashboard_adm.php" class="back-link">⬅ Voltar ao painel administrativo</a>
    </div>
</body>
</html>