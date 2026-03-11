<?php
// Iniciar sessão para manter os dados entre requisições
session_start();

// Inicializar arrays se não existirem
if (!isset($_SESSION['produtos'])) {
    $_SESSION['produtos'] = [
        'nomes' => [],
        'categorias' => [],
        'quantidades' => [],
        'precos' => []
    ];
}

// Definir a ação atual (padrão é menu)
$acao_atual = isset($_GET['acao']) ? $_GET['acao'] : 'menu';
$mensagem = '';
$tipo_mensagem = '';

// Processar ações
switch ($acao_atual) {
    case 'cadastrar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
            if (count($_SESSION['produtos']['nomes']) >= 10) {
                $mensagem = 'Limite de 10 produtos atingido!';
                $tipo_mensagem = 'erro';
            } else {
                $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
                $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
                $quantidade = isset($_POST['quantidade']) ? trim($_POST['quantidade']) : '';
                $preco = isset($_POST['preco']) ? trim($_POST['preco']) : '';

                $erros = [];

                if (empty($nome)) {
                    $erros[] = 'Nome do produto é obrigatório';
                }

                if (empty($categoria)) {
                    $erros[] = 'Categoria é obrigatória';
                }

                if (empty($quantidade)) {
                    $erros[] = 'Quantidade é obrigatória';
                } elseif (!is_numeric($quantidade) || intval($quantidade) < 0) {
                    $erros[] = 'Quantidade deve ser um número positivo';
                }

                if (empty($preco)) {
                    $erros[] = 'Preço é obrigatório';
                } elseif (!is_numeric($preco) || floatval($preco) < 0) {
                    $erros[] = 'Preço deve ser um número positivo';
                }

                if (empty($erros)) {
                    $_SESSION['produtos']['nomes'][] = $nome;
                    $_SESSION['produtos']['categorias'][] = $categoria;
                    $_SESSION['produtos']['quantidades'][] = intval($quantidade);
                    $_SESSION['produtos']['precos'][] = floatval($preco);

                    $mensagem = 'Produto cadastrado com sucesso!';
                    $tipo_mensagem = 'sucesso';

                    // Limpar formulário após sucesso
                    $_POST = [];
                } else {
                    $mensagem = 'Erros encontrados:<br>' . implode('<br>', $erros);
                    $tipo_mensagem = 'erro';
                }
            }
        }
        break;

    case 'deletar':
        if (isset($_GET['id'])) {
            $indice = intval($_GET['id']);
            if ($indice >= 0 && $indice < count($_SESSION['produtos']['nomes'])) {
                array_splice($_SESSION['produtos']['nomes'], $indice, 1);
                array_splice($_SESSION['produtos']['categorias'], $indice, 1);
                array_splice($_SESSION['produtos']['quantidades'], $indice, 1);
                array_splice($_SESSION['produtos']['precos'], $indice, 1);

                $mensagem = 'Produto removido com sucesso!';
                $tipo_mensagem = 'sucesso';

                $acao_atual = 'listar';
            }
        }
        break;

    case 'buscar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
            $_SESSION['termo_busca'] = isset($_POST['termo']) ? trim($_POST['termo']) : '';
        }
        break;

    case 'estoque_baixo':
        // Define valor mínimo para estoque baixo
        $limite_estoque = 5;
        break;

    case 'sair':
        // Sair é processado no HTML após exibir mensagem
        break;
    
    case 'confirmar_sair':
        session_destroy();
        header('Location: ?');
        exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Controle de Produtos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .content {
            padding: 30px;
        }

        .mensagem {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid;
        }

        .mensagem.sucesso {
            background-color: #d4edda;
            color: #155724;
            border-color: #28a745;
        }

        .mensagem.erro {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .form-section {
            background: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
        }

        .form-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.3em;
        }

        .form-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-control {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input,
        select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }

        button {
            grid-column: 1 / -1;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .produtos-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.3em;
        }

        .info-produtos {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            color: #1565c0;
            border-left: 4px solid #1565c0;
        }

        .produtos-vazio {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background-color: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .btn-deletar {
            background-color: #f44336;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-deletar:hover {
            background-color: #d32f2f;
        }

        .preco {
            font-weight: bold;
            color: #28a745;
        }

        .total-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
            color: #856404;
            margin-top: 20px;
            font-weight: bold;
        }

        .menu-principal {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
        }

        .menu-btn {
            padding: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .menu-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .menu-btn.sair {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            grid-column: 1 / -1;
        }

        .menu-btn.sair:hover {
            box-shadow: 0 10px 30px rgba(244, 67, 54, 0.4);
        }

        .breadcrumb {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .btn-voltar {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .btn-voltar:hover {
            background: #764ba2;
        }

        .resultado-busca,
        .resultado-estoque,
        .resultado-total {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            margin-top: 20px;
        }

        .nenhum-resultado {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }

        .tela-despedida {
            text-align: center;
            padding: 80px 40px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 8px;
            margin: 40px 0;
        }

        .tela-despedida h2 {
            font-size: 2.5em;
            color: #667eea;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .tela-despedida p {
            font-size: 1.2em;
            color: #555;
            margin: 15px 0;
            line-height: 1.6;
        }

        .tela-despedida .emoji {
            font-size: 3em;
            margin-bottom: 20px;
            display: block;
        }

        .tela-despedida .stats {
            background: white;
            padding: 30px;
            border-radius: 8px;
            margin: 30px 0;
            border: 2px solid #667eea;
            display: inline-block;
        }

        .tela-despedida .stats p {
            font-size: 1.1em;
            margin: 10px 0;
        }

        .tela-despedida .stats strong {
            color: #667eea;
        }

        .btn-grupo {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .btn-novo {
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-novo:hover {
            background: #764ba2;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-sair {
            background: #f44336;
        }

        .btn-sair:hover {
            background: #d32f2f;
            box-shadow: 0 5px 15px rgba(244, 67, 54, 0.4);
        }

        @media (max-width: 768px) {
            .form-group {
                grid-template-columns: 1fr;
            }

            .btn-grupo {
                flex-direction: column;
            }

            .btn-novo {
                width: 100%;
            }

            .menu-principal {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 1.8em;
            }

            table {
                font-size: 0.9em;
            }

            th,
            td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Sistema de Controle de Produtos</h1>
            <p>Gerencie seus produtos de forma simples e eficiente</p>
        </div>

        <div class="content">
            <!-- Exibir mensagens -->
            <?php if (!empty($mensagem)): ?>
                <div class="mensagem <?php echo $tipo_mensagem; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <!-- MENU PRINCIPAL -->
            <?php if ($acao_atual === 'menu'): ?>
                <div class="produtos-section">
                    <h2 style="text-align: center; margin-bottom: 10px;">Bem-vindo ao Sistema de Controle de Produtos!</h2>
                    <p style="text-align: center; color: #666; margin-bottom: 30px;">Selecione uma opção abaixo para começar</p>

                    <div class="menu-principal">
                        <a href="?acao=cadastrar" class="menu-btn">
                            ➕ Cadastrar Produto
                        </a>
                        <a href="?acao=listar" class="menu-btn">
                            📋 Listar Produtos
                        </a>
                        <a href="?acao=buscar" class="menu-btn">
                            🔍 Buscar Produto
                        </a>
                        <a href="?acao=estoque_baixo" class="menu-btn">
                            ⚠️ Estoque Baixo
                        </a>
                        <a href="?acao=total_estoque" class="menu-btn">
                            💰 Calcular Total
                        </a>
                        <a href="?acao=sair" class="menu-btn sair">
                            🚪 Sair
                        </a>
                    </div>
                </div>

            <!-- CADASTRAR PRODUTO -->
            <?php elseif ($acao_atual === 'cadastrar'): ?>
                <div class="breadcrumb">
                    <a href="?acao=menu">Menu</a> > Cadastrar Produto
                </div>

                <div class="form-section">
                    <h2>➕ Cadastrar Novo Produto</h2>
                    <p style="color: #666; margin-bottom: 20px;">Produtos cadastrados: <strong><?php echo count($_SESSION['produtos']['nomes']); ?>/10</strong></p>

                    <form method="POST" action="?acao=cadastrar">
                        <div class="form-group">
                            <div class="form-control">
                                <label for="nome">Nome do Produto *</label>
                                <input type="text" id="nome" name="nome" required placeholder="Ex: Notebook" value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>">
                            </div>
                            <div class="form-control">
                                <label for="categoria">Categoria *</label>
                                <select id="categoria" name="categoria" required>
                                    <option value="">-- Selecione uma categoria --</option>
                                    <option value="Eletrônicos" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] === 'Eletrônicos') ? 'selected' : ''; ?>>Eletrônicos</option>
                                    <option value="Alimentos" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] === 'Alimentos') ? 'selected' : ''; ?>>Alimentos</option>
                                    <option value="Roupas" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] === 'Roupas') ? 'selected' : ''; ?>>Roupas</option>
                                    <option value="Livros" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] === 'Livros') ? 'selected' : ''; ?>>Livros</option>
                                    <option value="Higiene" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] === 'Higiene') ? 'selected' : ''; ?>>Higiene</option>
                                    <option value="Móveis" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] === 'Móveis') ? 'selected' : ''; ?>>Móveis</option>
                                    <option value="Outro" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] === 'Outro') ? 'selected' : ''; ?>>Outro</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-control">
                                <label for="quantidade">Quantidade em Estoque *</label>
                                <input type="number" id="quantidade" name="quantidade" required min="0" placeholder="Ex: 50" value="<?php echo isset($_POST['quantidade']) ? htmlspecialchars($_POST['quantidade']) : ''; ?>">
                            </div>
                            <div class="form-control">
                                <label for="preco">Preço Unitário (R$) *</label>
                                <input type="number" id="preco" name="preco" required min="0" step="0.01" placeholder="Ex: 99.99" value="<?php echo isset($_POST['preco']) ? htmlspecialchars($_POST['preco']) : ''; ?>">
                            </div>
                        </div>

                        <button type="submit" name="salvar" value="1">Cadastrar Produto</button>
                    </form>

                    <a href="?acao=menu" class="btn-voltar">← Voltar ao Menu</a>
                </div>

            <!-- LISTAR PRODUTOS -->
            <?php elseif ($acao_atual === 'listar'): ?>
                <div class="breadcrumb">
                    <a href="?acao=menu">Menu</a> > Listar Produtos
                </div>

                <div class="produtos-section">
                    <h2>📋 Produtos Cadastrados</h2>
                    <div class="info-produtos">
                        Total de produtos: <strong><?php echo count($_SESSION['produtos']['nomes']); ?>/10</strong>
                    </div>

                    <?php if (count($_SESSION['produtos']['nomes']) === 0): ?>
                        <div class="produtos-vazio">
                            Nenhum produto cadastrado. <a href="?acao=cadastrar">Clique aqui</a> para adicionar um novo produto!
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>Categoria</th>
                                    <th>Quantidade</th>
                                    <th>Preço Unitário</th>
                                    <th>Valor Total</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_geral = 0;
                                for ($i = 0; $i < count($_SESSION['produtos']['nomes']); $i++):
                                    $valor_total = $_SESSION['produtos']['quantidades'][$i] * $_SESSION['produtos']['precos'][$i];
                                    $total_geral += $valor_total;
                                ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><?php echo htmlspecialchars($_SESSION['produtos']['nomes'][$i]); ?></td>
                                        <td><?php echo htmlspecialchars($_SESSION['produtos']['categorias'][$i]); ?></td>
                                        <td><?php echo $_SESSION['produtos']['quantidades'][$i]; ?></td>
                                        <td class="preco">R$ <?php echo number_format($_SESSION['produtos']['precos'][$i], 2, ',', '.'); ?></td>
                                        <td class="preco">R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></td>
                                        <td>
                                            <a href="?acao=deletar&id=<?php echo $i; ?>" class="btn-deletar" onclick="return confirm('Tem certeza que deseja remover este produto?')">Remover</a>
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>

                        <div class="total-info">
                            Valor total em estoque: R$ <?php echo number_format($total_geral, 2, ',', '.'); ?>
                        </div>
                    <?php endif; ?>

                    <a href="?acao=menu" class="btn-voltar">← Voltar ao Menu</a>
                </div>

            <!-- BUSCAR PRODUTO -->
            <?php elseif ($acao_atual === 'buscar'): ?>
                <div class="breadcrumb">
                    <a href="?acao=menu">Menu</a> > Buscar Produto
                </div>

                <div class="form-section">
                    <h2>🔍 Buscar Produto pelo Nome</h2>

                    <form method="POST" action="?acao=buscar">
                        <div class="form-group full">
                            <div class="form-control">
                                <label for="termo">Digite o nome do produto a buscar *</label>
                                <input type="text" id="termo" name="termo" required placeholder="Ex: Notebook" value="<?php echo isset($_SESSION['termo_busca']) ? htmlspecialchars($_SESSION['termo_busca']) : ''; ?>">
                            </div>
                        </div>

                        <button type="submit" name="buscar" value="1">Buscar</button>
                    </form>

                    <?php
                    if (isset($_SESSION['termo_busca']) && !empty($_SESSION['termo_busca'])):
                        $termo = strtolower($_SESSION['termo_busca']);
                        $produtos_encontrados = [];

                        for ($i = 0; $i < count($_SESSION['produtos']['nomes']); $i++):
                            if (strpos(strtolower($_SESSION['produtos']['nomes'][$i]), $termo) !== false):
                                $produtos_encontrados[] = $i;
                            endif;
                        endfor;

                        if (!empty($produtos_encontrados)):
                    ?>
                        <div class="resultado-busca">
                            <h3>Resultados da Busca</h3>
                            <p>Encontrados <?php echo count($produtos_encontrados); ?> produto(s) com o termo "<strong><?php echo htmlspecialchars($_SESSION['termo_busca']); ?></strong>"</p>

                            <table style="margin-top: 15px;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Categoria</th>
                                        <th>Quantidade</th>
                                        <th>Preço</th>
                                        <th>Valor Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($produtos_encontrados as $idx):
                                        $valor = $_SESSION['produtos']['quantidades'][$idx] * $_SESSION['produtos']['precos'][$idx];
                                    ?>
                                        <tr>
                                            <td><?php echo $idx + 1; ?></td>
                                            <td><?php echo htmlspecialchars($_SESSION['produtos']['nomes'][$idx]); ?></td>
                                            <td><?php echo htmlspecialchars($_SESSION['produtos']['categorias'][$idx]); ?></td>
                                            <td><?php echo $_SESSION['produtos']['quantidades'][$idx]; ?></td>
                                            <td class="preco">R$ <?php echo number_format($_SESSION['produtos']['precos'][$idx], 2, ',', '.'); ?></td>
                                            <td class="preco">R$ <?php echo number_format($valor, 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php
                        else:
                    ?>
                        <div class="resultado-busca">
                            <p class="nenhum-resultado">Nenhum produto encontrado com o termo "<strong><?php echo htmlspecialchars($_SESSION['termo_busca']); ?></strong>"</p>
                        </div>
                    <?php
                        endif;
                    endif;
                    ?>

                    <a href="?acao=menu" class="btn-voltar">← Voltar ao Menu</a>
                </div>

            <!-- ESTOQUE BAIXO -->
            <?php elseif ($acao_atual === 'estoque_baixo'): ?>
                <div class="breadcrumb">
                    <a href="?acao=menu">Menu</a> > Estoque Baixo
                </div>

                <div class="produtos-section">
                    <h2>⚠️ Produtos com Estoque Baixo</h2>
                    <div class="info-produtos">
                        Mostrando produtos com estoque ≤ 5 unidades
                    </div>

                    <?php
                    $limite_estoque = 5;
                    $produtos_baixo = [];

                    for ($i = 0; $i < count($_SESSION['produtos']['nomes']); $i++):
                        if ($_SESSION['produtos']['quantidades'][$i] <= $limite_estoque):
                            $produtos_baixo[] = $i;
                        endif;
                    endfor;

                    if (empty($produtos_baixo)):
                    ?>
                        <div class="nenhum-resultado">
                            ✓ Excelente! Nenhum produto possui estoque baixo.
                        </div>
                    <?php
                    else:
                    ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>Categoria</th>
                                    <th>Quantidade</th>
                                    <th>Preço Unitário</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($produtos_baixo as $idx):
                                    $status = $_SESSION['produtos']['quantidades'][$idx] === 0 ? 'FORA DE ESTOQUE' : 'ESTOQUE BAIXO';
                                    $cor_status = $_SESSION['produtos']['quantidades'][$idx] === 0 ? '#d32f2f' : '#ff9800';
                                ?>
                                    <tr>
                                        <td><?php echo $idx + 1; ?></td>
                                        <td><?php echo htmlspecialchars($_SESSION['produtos']['nomes'][$idx]); ?></td>
                                        <td><?php echo htmlspecialchars($_SESSION['produtos']['categorias'][$idx]); ?></td>
                                        <td style="font-weight: bold; color: #d32f2f;"><?php echo $_SESSION['produtos']['quantidades'][$idx]; ?></td>
                                        <td class="preco">R$ <?php echo number_format($_SESSION['produtos']['precos'][$idx], 2, ',', '.'); ?></td>
                                        <td style="font-weight: bold; color: <?php echo $cor_status; ?>;"><?php echo $status; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <a href="?acao=menu" class="btn-voltar">← Voltar ao Menu</a>
                </div>

            <!-- CALCULAR TOTAL DO ESTOQUE -->
            <?php elseif ($acao_atual === 'total_estoque'): ?>
                <div class="breadcrumb">
                    <a href="?acao=menu">Menu</a> > Calcular Total
                </div>

                <div class="produtos-section">
                    <h2>💰 Valor Total do Estoque</h2>

                    <?php if (count($_SESSION['produtos']['nomes']) === 0): ?>
                        <div class="nenhum-resultado">
                            Nenhum produto cadastrado no momento.
                        </div>
                    <?php else: ?>
                        <table style="margin-bottom: 30px;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>Categoria</th>
                                    <th>Quantidade</th>
                                    <th>Preço Unitário</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_geral = 0;
                                for ($i = 0; $i < count($_SESSION['produtos']['nomes']); $i++):
                                    $subtotal = $_SESSION['produtos']['quantidades'][$i] * $_SESSION['produtos']['precos'][$i];
                                    $total_geral += $subtotal;
                                ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><?php echo htmlspecialchars($_SESSION['produtos']['nomes'][$i]); ?></td>
                                        <td><?php echo htmlspecialchars($_SESSION['produtos']['categorias'][$i]); ?></td>
                                        <td><?php echo $_SESSION['produtos']['quantidades'][$i]; ?></td>
                                        <td class="preco">R$ <?php echo number_format($_SESSION['produtos']['precos'][$i], 2, ',', '.'); ?></td>
                                        <td class="preco">R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>

                        <div class="resultado-total">
                            <h3 style="color: #667eea; margin-bottom: 15px;">Resumo do Estoque</h3>
                            <p style="margin: 10px 0; font-size: 1.1em;">
                                <strong>Total de produtos:</strong> <?php echo count($_SESSION['produtos']['nomes']); ?>
                            </p>
                            <p style="margin: 10px 0; font-size: 1.1em;">
                                <strong>Quantidade total de itens:</strong> <?php echo array_sum($_SESSION['produtos']['quantidades']); ?>
                            </p>
                            <p style="margin: 15px 0; font-size: 1.3em; color: #28a745; font-weight: bold; border-top: 2px solid #ddd; padding-top: 15px;">
                                💵 Valor Total do Estoque: R$ <?php echo number_format($total_geral, 2, ',', '.'); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <a href="?acao=menu" class="btn-voltar">← Voltar ao Menu</a>
                </div>

            <!-- TELA DE DESPEDIDA -->
            <?php elseif ($acao_atual === 'sair'): ?>
                <div class="tela-despedida">
                    <span class="emoji">👋</span>
                    <h2>Até Logo!</h2>
                    <p>Obrigado por usar o <strong>Sistema de Controle de Produtos</strong></p>
                    <p style="color: #999; font-size: 1em;">Seu trabalho foi salvo com sucesso durante esta sessão</p>

                    <?php if (count($_SESSION['produtos']['nomes']) > 0): ?>
                        <div class="stats">
                            <p><strong>📊 Resumo da Sessão:</strong></p>
                            <p>Total de produtos cadastrados: <strong><?php echo count($_SESSION['produtos']['nomes']); ?></strong></p>
                            <p>Quantidade total de itens: <strong><?php echo array_sum($_SESSION['produtos']['quantidades']); ?></strong> unidades</p>
                            <?php
                            $total_sessao = 0;
                            for ($i = 0; $i < count($_SESSION['produtos']['nomes']); $i++):
                                $total_sessao += $_SESSION['produtos']['quantidades'][$i] * $_SESSION['produtos']['precos'][$i];
                            endfor;
                            ?>
                            <p>Valor total do estoque: <strong>R$ <?php echo number_format($total_sessao, 2, ',', '.'); ?></strong></p>
                        </div>
                    <?php endif; ?>

                    <div class="btn-grupo">
                        <a href="?acao=menu" class="btn-novo">
                            ↩️ Voltar ao Menu
                        </a>
                        <a href="?acao=confirmar_sair" class="btn-novo btn-sair" onclick="return confirm('Tem certeza que deseja sair? Os dados serão mantidos na próxima sessão.')">
                            🚪 Encerrar Sistema
                        </a>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>
