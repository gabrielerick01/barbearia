<?php
// Pega apenas o nome do arquivo atual, sem caminho
$pagina_atual = basename($_SERVER['PHP_SELF']);
?>

<nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina_atual == 'index.php') ? 'active' : ''; ?>" href="/Barbearia/project/index.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina_atual == 'clientes.php') ? 'active' : ''; ?>" href="/Barbearia/project/pages/clientes.php">
                    <i class="fas fa-users me-2"></i>
                    Clientes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina_atual == 'servicos.php') ? 'active' : ''; ?>" href="/Barbearia/project/pages/servicos.php">
                    <i class="fas fa-list me-2"></i>
                    Serviços
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina_atual == 'receitas.php') ? 'active' : ''; ?>" href="/Barbearia/project/pages/receitas.php">
                    <i class="fas fa-cash-register me-2"></i>
                    Atendimentos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina_atual == 'despesas.php') ? 'active' : ''; ?>" href="/Barbearia/project/pages/despesas.php">
                    <i class="fas fa-credit-card me-2"></i>
                    Despesas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina_atual == 'relatorios.php') ? 'active' : ''; ?>" href="/Barbearia/project/pages/relatorios.php">
                    <i class="fas fa-chart-bar me-2"></i>
                    Relatórios
                </a>
            </li>
            <?php if (isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/Barbearia/project/pages/usuarios.php">
                        <i class="fas fa-users-cog me-2"></i>
                        Usuários
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
