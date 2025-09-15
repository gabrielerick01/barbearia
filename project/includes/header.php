<?php include_once __DIR__ . '/../config/config.php'; ?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>index.php">
            <img src="<?= BASE_URL ?>img/logo.jpg" alt="Logo" style="height: 40px; margin-right: 8px;">
            SS | Barbearia
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
         <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo getUserName(); ?>
                        <?php if (isAdmin()): ?>
                            <span class="badge bg-warning ms-1">Admin</span>
                        <?php endif; ?>
                    </a>
                     <ul class="dropdown-menu">
                        <?php if (isAdmin()): ?>
                            <li><a class="dropdown-item" href="pages/usuarios.php"><i class="fas fa-users-cog me-2"></i>Usuários</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
