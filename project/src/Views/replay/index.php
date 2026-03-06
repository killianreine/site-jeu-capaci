<?php

use App\Models\Classes\Plateau;
use App\Models\Classes\Cases;

/**
 * Génère un plateau statique pour le replay.
 *
 * @param Plateau $plateau
 * @return string HTML du plateau
 */
function generateReplayBoard(Plateau $plateau, $caseArrivee, $aManger): string
{
	$html = '<div class="content">';
	$html .= '<div class="plateau replay-plateau">';

	if ($caseArrivee != -1) {
		$xArrivee = $caseArrivee[0];
		$yArrivee = $caseArrivee[1];
	}

	for ($ligne = 0; $ligne < Plateau::TAILLE; $ligne++) {
		for ($colonne = 0; $colonne < Plateau::TAILLE; $colonne++) {

			$casePlateau = $plateau->getCase($ligne, $colonne);
			$piece = $casePlateau->getPiece();

			$class = "case";

			if (isset($xArrivee) && $ligne === $xArrivee && $colonne === $yArrivee) {
				$class .= $aManger ? " case-attaquable" : " case-selected";
			}

			$html .= '<div class="' . $class . '">';

			if ($piece) {

				$type = strtolower($piece->getForme()->name);
				$couleur = strtolower($piece->getJoueur()->getCouleur());

				$imagePath = BASE_URL . "/assets/pieces/{$type}/{$couleur}/{$type}.svg";

				$html .= sprintf(
					'<img src="%s" alt="%s %s">',
					$imagePath,
					$couleur,
					$type
				);
			}

			$html .= '</div>';
		}
	}

	$html .= '</div>';
	$html .= '</div>';

	return $html;
}
?>
<div class = "replay-ui">

  <div class="replay-info">
	<div class="players">
			<span class="player"><?= htmlspecialchars($username1) ?></span>
			<span class="vs">VS</span>
			<span class="player"><?= htmlspecialchars($username2) ?></span>
		</div>

		<div class="tour-info">
			Tour <strong><?= $tour ?></strong>
		</div>
	</div>

	<form method="POST" action="<?= BASE_URL?>/replay/action" class="replay-controls">

		<input type="hidden" name="partie_id" value="<?= $partie_id ?>">
		<input type="hidden" name="tour" value="<?= $tour ?>">

		<button type="submit" name="action" value="0" class="btn-replay btn-prev">
			⬅ Précédent
		</button>

		<button type="submit" name="action" value="1" class="btn-replay btn-next">
			Suivant ➡
		</button>

	</form>
</div>

<?= generateReplayBoard($plateau,$case, $aManger, $username1, $username2); ?>
