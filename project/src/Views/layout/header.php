<header class="site-header">
	<div class="header-content">
		<div class="logo-section">
			<div class="logo-text">
				<h1>Capaci</h1>
				<p class="tagline">Pierre - Feuille - Ciseaux</p>
			</div>
		</div>

		<nav class="header-nav">
			<a href="<?= BASE_URL ?>/" class="nav-link">Accueil</a>
			<a href="<?= BASE_URL ?>/creer-partie" class="nav-link">Nouvelle partie</a>

			<?php if ($isConnected): ?>
				<div class = "dropdown" >
					<a href="<?= BASE_URL ?>/mon-compte" class="nav-link"><?= $_SESSION['users_name'] ?? "toto" ?></a>

					<div class = "dropdown-menu">
						<a href="<?= BASE_URL?>/logout">Déconnexion</a>
					</div>
				</div>
			<?php else: ?>
				<a href="<?= BASE_URL ?>/login" class="nav-link">Connexion</a>
			<?php endif; ?>
		</nav>
	</div>
</header>
