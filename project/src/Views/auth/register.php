<div class="content">
	<form method="POST" action="<?= BASE_URL ?>/register" class="register-form">

		<?php if (isset($errors['all'])): ?>
		<div class="form-group">
			<p class="error error-global">
				<?= htmlspecialchars($errors['all']) ?>
			</p>
		</div>
		<?php endif; ?>

		<div class="form-group">
			<label>Email</label>
			<input class="textfield <?= isset($errors['email']) ? 'error-input' : '' ?>"
				   type="email"
				   name="email"
				   value="<?= htmlspecialchars($old['email'] ?? '') ?>"
				   required />
			<?php if (isset($errors['email'])): ?>
				<p class="error"><?= htmlspecialchars($errors['email']) ?></p>
			<?php endif; ?>
		</div>

		<div class="form-group">
			<label>Nom d'utilisateur</label>
			<input class="textfield <?= isset($errors['users']) ? 'error-input' : '' ?>"
				   type="text"
				   name="username"
				   value="<?= htmlspecialchars($old['username'] ?? '') ?>"
				   required />
			<?php if (isset($errors['username'])): ?>
				<p class="error"><?= htmlspecialchars($errors['username']) ?></p>
			<?php endif; ?>
		</div>

		<div class="form-group">
			<label>Mot de passe</label>
			<input class="textfield <?= isset($errors['mdp']) ? 'error-input' : '' ?>"
				   type="password"
				   name="mdp"
				   required />
			<?php if (isset($errors['mdp'])): ?>
				<p class="error"><?= htmlspecialchars($errors['mdp']) ?></p>
			<?php endif; ?>
		</div>

		<div class="form-group">
			<label>Confirmer le mot de passe</label>
			<input class="textfield <?= isset($errors['confirmMdp']) ? 'error-input' : '' ?>"
				   type="password"
				   name="confirmMdp"
				   required />
			<?php if (isset($errors['confirmMdp'])): ?>
				<p class="error"><?= htmlspecialchars($errors['confirmMdp']) ?></p>
			<?php endif; ?>
		</div>

		<button class="btn" type="submit">S'inscrire</button>

		<p class="switch">
			Déjà un compte ?
			<a href="<?= BASE_URL ?>/login">Se connecter</a>
		</p>

	</form>
</div>
