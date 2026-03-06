<input type="checkbox" id="join-modal-toggle">

<div class="home-wrapper">

    <div class="hero-actions">
        <form method="POST" action="<?= BASE_URL ?>/creer-partie" style="display:contents;">
            <button class="btn-hero btn-hero-primary" type="submit" name="type_partie" value="privee">
                <img class="btn-icon" src="<?= BASE_URL ?>/assets/icons/play.svg" alt="">
                Partie privée
            </button>
            <button class="btn-hero btn-hero-secondary" type="submit" name="type_partie" value="publique">
                <img class="btn-icon" src="<?= BASE_URL ?>/assets/icons/play.svg" alt="">
                Partie publique
            </button>
        </form>
        <label for="join-modal-toggle" class="btn-hero btn-hero-code">
            🔑 Rejoindre via un code
        </label>
    </div>

    <div class="lobby-grid">

        <div class="lobby-card">
            <div class="lobby-card-header">
                <span class="card-icon">🎮</span>
                <h3>Mes parties en cours</h3>
            </div>
            <table class="lobby-table">
                <thead>
                    <tr>
                        <th>Adversaire</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($parties['privees'])): ?>
                        <?php foreach ($parties['privees'] as $partie): ?>
                            <?php
                                $adversaire = $partie['adversaire'] ?? $partie['nom_hote'] ?? 'En attente...';
                                $dateAff    = $partie['date_aff'] ?? '—';
                            ?>
                            <tr>
                                <td><span class="player-name"><?= htmlspecialchars($adversaire) ?></span></td>
                                <td><span class="date-label"><?= $dateAff ?></span></td>
                                <td>
                                    <form method="POST" action="<?= BASE_URL ?>/rejoindre-partie">
                                        <input type="hidden" name="code" value="<?= $partie['code'] ?>">
                                        <button type="submit" class="btn-table btn-resume">Reprendre</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="empty-row"><td colspan="3">Aucune partie active.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="lobby-card">
            <div class="lobby-card-header">
                <span class="card-icon">🌍</span>
                <h3>Parties publiques</h3>
            </div>
            <table class="lobby-table">
                <thead>
                    <tr>
                        <th>Hôte</th>
                        <th>Joueurs</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($parties['publiques'])): ?>
                        <?php foreach ($parties['publiques'] as $partie): ?>
                            <tr>
                                <td><span class="player-name"><?= htmlspecialchars($partie['nom_hote'] ?? '—') ?></span></td>
                                <td><span class="badge-players"><?= $partie['nb_joueurs'] ?> / <?= $partie['max_joueurs'] ?></span></td>
                                <td>
                                    <form method="POST" action="<?= BASE_URL ?>/rejoindre-partie">
                                        <input type="hidden" name="code" value="<?= $partie['code'] ?>">
                                        <button type="submit" class="btn-table btn-join">Rejoindre</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="empty-row"><td colspan="3">Aucune partie disponible.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<div class="join-modal">
    <div class="join-modal-box">
        <h2>🔑 Rejoindre via un code</h2>
        <form method="POST" action="<?= BASE_URL ?>/rejoindre-partie">
            <input
                class="join-input"
                type="text"
                name="code"
                placeholder="123456"
                pattern="[0-9]{6}"
                maxlength="6"
                required>
            <div class="join-modal-actions">
                <label for="join-modal-toggle" class="btn-cancel">Annuler</label>
                <button type="submit" class="btn-validate">Valider</button>
            </div>
        </form>
    </div>
</div>