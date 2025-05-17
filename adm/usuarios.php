<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/db.php';

// Verificar se o usuário pertence ao grupo administrador (id_grupo = 1)
$stmt = $pdo->prepare("SELECT id_grupo FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

if (!$usuario || $usuario['id_grupo'] != 1) {
    header('Location: ../menu.php');
    exit;
}

$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$equipamentos = $pdo->query("SELECT * FROM itens ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$laboratorios = $pdo->query("SELECT * FROM laboratorios ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="../assets/adm.css">
    <style>
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .admin-header h2 {
            margin: 0;
        }
        .tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .tab-btn {
            padding: 10px 15px;
            background-color: #ddd;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .tab-btn.active {
            background-color: #3498db;
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="admin-header">
        <h2>Painel Administrativo</h2>
        <a href="../menu.php" class="back-link">⬅ Voltar ao menu</a>
    </div>

    <div class="tabs">
        <button class="tab-btn active" data-tab="usuarios">Usuários</button>
        <button class="tab-btn" data-tab="laboratorios">Laboratórios/Sala</button>
        <button class="tab-btn" data-tab="equipamentos">Equipamentos</button>
    </div>

    <!-- ... tudo igual até aqui ... -->

<div id="usuarios" class="tab-content active">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <a href="cadastro_user.php" class="back-link" style="background-color: #2ecc71;">➕ Novo Usuário</a>
        <input type="text" id="searchUser" placeholder="🔍 Buscar por nome ou e-mail" style="padding: 8px; width: 250px; border-radius: 5px; border: 1px solid #ccc;">
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="userTable">
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= $usuario['id'] ?></td>
                    <td><?= htmlspecialchars($usuario['nome']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td><?= $usuario['status'] ?? 'Indefinido' ?></td>
                    <td class="actions">
                        <a href="editar_usuario.php?id=<?= $usuario['id'] ?>">✏️ Editar</a>
                        <a href="excluir_usuario.php?id=<?= $usuario['id'] ?>" onclick="return confirm('Deseja excluir este usuário?')">❌ Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


    <div id="laboratorios" class="tab-content">
        <div style="margin-bottom: 10px;">
            <a href="cadastro_laboratorio.php" class="back-link" style="background-color: #2ecc71;">➕ Novo Laboratório</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($laboratorios as $lab): ?>
                    <tr>
                        <td><?= $lab['id'] ?></td>
                        <td><?= htmlspecialchars($lab['nome']) ?></td>
                        <td><?= htmlspecialchars($lab['descricao']) ?></td>
                        <td><?= $lab['status'] ?></td>
                        <td class="actions">
                            <a href="editar_laboratorio.php?id=<?= $lab['id'] ?>">✏️ Editar</a>
                            <a href="excluir_laboratorio.php?id=<?= $lab['id'] ?>" onclick="return confirm('Deseja excluir este laboratório?')">❌ Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="equipamentos" class="tab-content">
        <div style="margin-bottom: 10px;">
            <a href="cadastro_equipamento.php" class="back-link" style="background-color: #2ecc71;">➕ Novo Equipamento</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipamentos as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= htmlspecialchars($item['nome']) ?></td>
                        <td><?= $item['status'] ?></td>
                        <td class="actions">
                            <a href="editar_equipamento.php?id=<?= $item['id'] ?>">✏️ Editar</a>
                            <a href="excluir_equipamento.php?id=<?= $item['id'] ?>" onclick="return confirm('Deseja excluir este equipamento?')">❌ Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Alternância de abas
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.getAttribute('data-tab');

            tabButtons.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            btn.classList.add('active');
            document.getElementById(tab).classList.add('active');
        });
    });

    // Filtro de busca de usuários
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchUser');
        const userRows = document.querySelectorAll('#userTable tr');

        if (searchInput) {
            searchInput.addEventListener('keyup', function () {
                const search = this.value.toLowerCase();

                userRows.forEach(row => {
                    const nome = row.cells[1].textContent.toLowerCase();
                    const email = row.cells[2].textContent.toLowerCase();
                    const match = nome.includes(search) || email.includes(search);
                    row.style.display = match ? '' : 'none';
                });
            });
        }
    });
</script>



</body>
</html>