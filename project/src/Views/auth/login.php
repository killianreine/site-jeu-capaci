<div class="content">
	<form method="POST" action="<?= BASE_URL ?>/login" class="login-form">

		<?php if (isset($errors['all'])): ?>
			<div class="form-group">
				<p class="error error-global">
					<?= htmlspecialchars($errors['all']) ?>
				</p>
			</div>
		<?php endif; ?>

		<div class="form-group">
			<label>Email</label>
			<input 	class="textfield"
					value="<?= $old['email'] ?? "" ?>"
				   	type="email"
				  	name="email"
				   	required />
		</div>

		<div class="form-group">
			<label>Mot de passe</label>
			<input class="textfield"
				   type="password"
				   name="mdp"
				   required />
		</div>

		<button class="btn" type="submit">Se connecter</button>

		<p class="switch">
			Pas encore de compte ?
			<a href="<?= BASE_URL ?>/register">S'inscrire</a>
		</p>
	</form>
</div>
