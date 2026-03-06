<?php
$currentUsername = null;
if (isset($currentUserId)) {
    $users = \App\Config\Database::select('users', ['users_username'], ['users_id' => $currentUserId]);
    $currentUsername = $users[0]['users_username'] ?? null;
}
$isWinner = $currentUsername && $currentUsername === $gagnant;
?>

<?php if ($gameOver): ?>

<?php if ($isWinner): ?>
<div class="modal show game-over-modal winner-modal">
    <div class="modal-content winner-content">

        <div class="result-icon">🏆</div>
        <h2 class="result-title winner-title">Victoire !</h2>

        <div class="game-result">
            <p class="winner-text">
                Félicitations <strong><?= htmlspecialchars($gagnant) ?></strong> !
            </p>
            <p class="loser-text">
                <strong><?= htmlspecialchars($perdant) ?></strong>
                <?= htmlspecialchars($gameOver[1] ?? '') ?>
            </p>
        </div>

        <div class="modal-actions">
            <form method="POST" action="<?= BASE_URL ?>/recreer-partie" style="display:inline;">
                <button type="submit" name="reset_game" class="btn btn-rejouer">
                    Rejouer avec <?= htmlspecialchars($gagnant === $joueur1Name ? $joueur2Name : $joueur1Name) ?>
                </button>
            </form>
            <a href="<?= BASE_URL ?>/home" class="btn btn-accueil">Retour à l'accueil</a>
        </div>
    </div>
</div>

<?php else: ?>
<div class="modal show game-over-modal loser-modal">
    <div class="modal-content loser-content">

        <div class="result-icon">💀</div>
        <h2 class="result-title loser-title">Défaite</h2>

        <div class="game-result">
            <p class="loser-self-text">
                Tu <strong><?= htmlspecialchars($gameOver[1] ?? 'perdu') ?></strong>
            </p>
            <p class="winner-opponent-text">
                <strong><?= htmlspecialchars($gagnant) ?></strong> remporte la partie !
            </p>
        </div>

        <div class="modal-actions">
            <form method="POST" action="<?= BASE_URL ?>/recreer-partie" style="display:inline;">
                <button type="submit" name="reset_game" class="btn btn-rejouer-loser">
                    Revanche !
                </button>
            </form>
            <a href="<?= BASE_URL ?>/home" class="btn btn-accueil-loser">Retour à l'accueil</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>