<!DOCTYPE html>
<html lang="pt_br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Cadastro de Alunos</title>
   
</head>
<body>
    <h1>Sistema de Cadastro de Alunos</h1>
    
    <?php
    session_start();
    
    if (!isset($_SESSION['alunos'])) {
        $_SESSION['alunos'] = array();
    }
    
    $acao = isset($_POST['acao']) ? $_POST['acao'] : '';
    $nome = "";
    $idade = "";
    $curso = "";
    $nota_final = "";
    $search_nome = "";
    $search_result = null;
    $search_message = "";
    $errors = array();
    
    // Função para limpar e proteger os dados
    function clean_input($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }
    
    if ($acao === 'sair') {
        session_destroy();
        echo "<p style='color:blue;'>Sessão encerrada. Obrigado por usar o sistema!</p>";
        echo "<a href='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>Reiniciar</a>";
        exit;
    }
    
    if ($_SERVER["REQUEST_METHOD"] === "POST" && $acao === 'cadastrar') {
        // validação do nome
        if (empty($_POST["nome"])) {
            $errors[] = "O nome é obrigatório.";
        } else {
            $nome = clean_input($_POST["nome"]);
            if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/", $nome)) {
                $errors[] = "O nome só pode conter letras e espaços.";
            }
        }
    
        // Validação da idade
        if (empty($_POST["idade"])) {
            $errors[] = "A idade é obrigatória.";
        } else {
            $idade = clean_input($_POST["idade"]);
            if (!filter_var($idade, FILTER_VALIDATE_INT) || $idade < 0 || $idade > 120) {
                $errors[] = "Idade inválida.";
            }
        }
    
        // Validação do curso
        if (empty($_POST["curso"])) {
            $errors[] = "O curso é obrigatório.";
        } else {
            $curso = clean_input($_POST["curso"]);
        }
    
        // Validação da nota final
        if (empty($_POST["nota_final"])) {
            $errors[] = "A nota final é obrigatória.";
        } else {
            $nota_final = clean_input($_POST["nota_final"]);
            if (!is_numeric($nota_final) || $nota_final < 0 || $nota_final > 10) {
                $errors[] = "A nota final deve ser um número entre 0 e 10.";
            }
        }
    
        // Se não houver erros, grava e exibe os dados
        if (empty($errors)) {
            $aluno = array(
                'nome' => $nome,
                'idade' => $idade,
                'curso' => $curso,
                'nota_final' => $nota_final
            );
            $_SESSION['alunos'][] = $aluno;
    
            echo "<p style='color:green;'>Aluno cadastrado com sucesso!</p>";
            // Limpa valores
            $nome = $idade = $curso = $nota_final = "";
        }
    }
    
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['search_nome']) && $acao === 'buscar') {
        $search_nome = clean_input($_GET['search_nome']);
    
        $found = false;
        foreach ($_SESSION['alunos'] as $alunoCadastrado) {
            if (strcasecmp($alunoCadastrado['nome'], $search_nome) === 0) {
                $search_result = $alunoCadastrado;
                $found = true;
                break;
            }
        }
    
        if ($found) {
            $search_message = "<h3>Aluno encontrado:</h3>";
            $search_message .= "<p><strong>Nome:</strong> " . htmlspecialchars($search_result['nome']) . "</p>";
            $search_message .= "<p><strong>Idade:</strong> " . htmlspecialchars($search_result['idade']) . "</p>";
            $search_message .= "<p><strong>Curso:</strong> " . htmlspecialchars($search_result['curso']) . "</p>";
            $search_message .= "<p><strong>Nota:</strong> " . htmlspecialchars($search_result['nota_final']) . "</p>";
        } else {
            $search_message = "<p style='color:orange;'>Aluno não encontrado.</p>";
        }
    }
    ?>
    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label>Escolha uma opção:</label><br>
        <input type="radio" name="acao" value="cadastrar" <?php if ($acao === 'cadastrar') echo 'checked'; ?>> Cadastrar aluno<br>
        <input type="radio" name="acao" value="listar" <?php if ($acao === 'listar') echo 'checked'; ?>> Listar alunos<br>
        <input type="radio" name="acao" value="buscar" <?php if ($acao === 'buscar') echo 'checked'; ?>> Buscar aluno pelo nome<br>
        <input type="radio" name="acao" value="media" <?php if ($acao === 'media') echo 'checked'; ?>> Calcular média da turma<br>
        <input type="radio" name="acao" value="sair"> Sair<br><br>
        <input type="submit" value="Executar">
    </form>
    
    <?php if ($acao === 'cadastrar'): ?>
    <h2>Cadastrar Aluno</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="hidden" name="acao" value="cadastrar">
        <label>Nome:</label><br>
        <input type="text" name="nome" value="<?php echo $nome; ?>"><br><br>
    
        <label>Idade:</label><br>
        <input type="number" name="idade" value="<?php echo $idade; ?>"><br><br>
    
        <label>Curso:</label><br>
        <input type="text" name="curso" value="<?php echo $curso; ?>"><br><br>
    
        <label>Nota Final:</label><br>
        <input type="number" step="0.01" name="nota_final" value="<?php echo $nota_final; ?>"><br><br>
    
        <input type="submit" value="Cadastrar">
    </form>
    <?php endif; ?>
    
    <?php if ($acao === 'listar'): ?>
    <h2>Lista de Alunos Cadastrados</h2>
    <?php
    if (!empty($_SESSION['alunos'])) {
        echo "<table border='1' cellspacing='0' cellpadding='4'>";
        echo "<tr><th>Nome</th><th>Idade</th><th>Curso</th><th>Nota</th></tr>";
    
        foreach ($_SESSION['alunos'] as $alunoCadastrado) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($alunoCadastrado['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($alunoCadastrado['idade']) . "</td>";
            echo "<td>" . htmlspecialchars($alunoCadastrado['curso']) . "</td>";
            echo "<td>" . htmlspecialchars($alunoCadastrado['nota_final']) . "</td>";
            echo "</tr>";
        }
    
        echo "</table>";
    } else {
        echo "<p>Nenhum aluno cadastrado ainda.</p>";
    }
    ?>
    <?php endif; ?>
    
    <?php if ($acao === 'buscar'): ?>
    <h2>Buscar Aluno pelo Nome</h2>
    <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="hidden" name="acao" value="buscar">
        <label>Nome do aluno:</label><br>
        <input type="text" name="search_nome" value="<?php echo htmlspecialchars($search_nome); ?>"><br><br>
        <input type="submit" value="Buscar">
    </form>
    <?php echo $search_message; ?>
    <?php endif; ?>
    
    <?php if ($acao === 'media'): ?>
    <h2>Cálculo da Média da Turma</h2>
    <?php
    if (!empty($_SESSION['alunos'])) {
        $somaNotas = 0.0;
        $quantidade = count($_SESSION['alunos']);
    
        foreach ($_SESSION['alunos'] as $alunoCadastrado) {
            $somaNotas += floatval($alunoCadastrado['nota_final']);
        }
    
        $media = $somaNotas / $quantidade;
        echo "<p><strong>Média da turma:</strong> " . number_format($media, 2, ',', '.') . "</p>";
    } else {
        echo "<p style='color:orange;'>Não é possível calcular a média porque não existem alunos cadastrados.</p>";
    }
    ?>
    <?php endif; ?>
    
    <?php
    // Exibe erros, se houver
    if (!empty($errors)) {
        echo "<ul style='color:red;'>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    }
    ?>
</body>
</html>