<?php
declare(strict_types=1);

/**
 * Classe Pieces
 * @author Thomas   Boudeele    GrA
 * @author Louis    Hagues      GrA
 * @author Killian  Reine       GrA
 * 
 * Dernière modification : Vendredi 23 janvier 2026
 */
namespace App\Models\Classes;

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Plateau.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Cases.php';

require_once __DIR__.'/../enums/Formes.php';

use App\Models\Enums\Forme;
use App\Models\Classes\Plateau;
use App\Models\Classes\Cases;

/**
 * Représentation d'une pièce de jeu.
 * La logique de combat est déléguée à l'énumération Forme.
 */
class Piece
{
	/** @var int Nombre de pièces adjacentes */
	private int $nbPieceAutour = 0;

	/** @var bool Indique si la pièce n'a aucun voisin */
	private bool $estIsole = true;

	/** @var int Distance maximale de mouvement autorisée */
	private int $nbCaseDeplacement = 1;

	/** @var int Position verticale (Y) */
	private int $ligne;

	/** @var int Position horizontale (X) */
	private int $colonne;

	/** @var Forme Type de la pièce (UML: Forme) */
	private Forme $forme;

	/** @var Joueur Propriétaire de la pièce (UML: Joueur) */
	private Joueur $joueur;

	/**
	 * Constructeur de la pièce.
	 */
	public function __construct(Joueur $joueur, Forme $forme, int $ligne, int $colonne)
	{
		$this->joueur = $joueur;
		$this->forme = $forme;
		$this->ligne = $ligne;
		$this->colonne = $colonne;
	}

	/**
	 * Applique un déplacement de la pièce vers une nouvelle position.
	 * @param int $ligne
	 * @param int $colonne
	 */
	public function deplacement(Plateau $plateau, int $ligne, int $colonne): void
	{
		if (!$this->verifDeplacement($plateau, $ligne, $colonne)) {
			throw new \InvalidArgumentException("Déplacement invalide vers ($ligne, $colonne)");
		}

		$ancienneCase = $plateau->getCase($this->ligne, $this->colonne);
		if ($ancienneCase->getPiece() === $this) {
			$ancienneCase->setPiece(null);
		}

		$this->ligne = $ligne;
		$this->colonne = $colonne;

		$nouvelleCase = $plateau->getCase($ligne, $colonne);
		$nouvelleCase->setPiece($this);
	}

	/**
	 * Valide si un déplacement est possible selon les règles de mouvement.
	 * @param int $ligne
	 * @param int $colonne
	 * @return bool
	 */
	public function verifDeplacement(Plateau $plateau, int $xA, int $yA): bool
	{
		// 1. Vérification des limites du plateau
		if ($plateau->estDehors($xA, $yA)) {
			return false;
		}

		$xD = $this->ligne;
		$yD = $this->colonne;
		$dx = $xA - $xD;
		$dy = $yA - $yD;

		// 2. Doit obligatoirement bouger
		if ($dx === 0 && $dy === 0) {
			return false;
		}

		// 3. Direction autorisée : Horizontal, Vertical ou Diagonal (8 directions)
		$absDx = abs($dx);
		$absDy = abs($dy);
		$isDiagonal = ($absDx === $absDy);
		$isStraight = ($dx === 0 || $dy === 0);

		if (!$isDiagonal && !$isStraight) {
			return false;
		}

		// 4. Vérification de la portée (distance maximale)
		$distance = max($absDx, $absDy);
		if ($distance > $this->nbCaseDeplacement) {
			return false;
		}

		// // 5. Vérification du chemin (Pas de saut au-dessus des pièces)
		// if (!$this->isCheminLibre($plateau, $xD, $yD, $xA, $yA, $distance)) {
		//     return false;
		// }

		// 6. Vérification de la case d'arrivée
		$cible = $plateau->getCase($xA, $yA)->getPiece();
		if ($cible !== null && $cible->getJoueur() === $this->joueur) {
			return false;
		}

		return true;
	}

	/**
	 * Recalcule la portée maximale de déplacement de la pièce
	 * en fonction des pièces adjacentes (Haut, Bas, Gauche, Droite).
	 * @param Plateau $plateau
	 * @return int Nouvelle portée calculée
	 */
	public function recalculerPortee(Plateau $plateau): void{
		$portee = 0;

		// Coordonnées actuelles
		$x = $this->ligne;
		$y = $this->colonne;

		// Directions à vérifier : Haut, Bas, Gauche, Droite
		$directions = [
			[-1, 0], // Haut
			[1, 0],  // Bas
			[0, -1], // Gauche
			[0, 1],  // Droite
		];

		foreach ($directions as [$dx, $dy]) {
			$nx = $x + $dx;
			$ny = $y + $dy;

			// Vérifie que la case est dans le plateau
			if (!$plateau->estDehors($nx, $ny)) {
				$caseAdj = $plateau->getCase($nx, $ny);
				if ($caseAdj->getPiece() !== null) {
					$portee++;
				}
			}
		}

		$this->nbCaseDeplacement = $portee;
	}

	// --- GETTERS & SETTERS ---

	public function getLigne(): int { return $this->ligne; }
	public function setLigne(int $ligne): void { $this->ligne = $ligne; }

	public function getColonne(): int { return $this->colonne; }
	public function setColonne(int $colonne): void { $this->colonne = $colonne; }

	public function getForme(): Forme { return $this->forme; }
	public function setForme(Forme $forme): void { $this->forme = $forme; }

	public function getJoueur(): Joueur { return $this->joueur; }
	public function setJoueur(Joueur $joueur): void { $this->joueur = $joueur; }

	public function getNbPieceAutour(): int { return $this->nbPieceAutour; }
	public function setNbPieceAutour(int $nb): void { $this->nbPieceAutour = $nb; }

	public function isEstIsole(): bool { return $this->estIsole; }
	public function setEstIsole(bool $isole): void { $this->estIsole = $isole; }

	public function getNbCaseDeplacement(): int { return $this->nbCaseDeplacement; }
	public function setNbCaseDeplacement(int $nb): void { $this->nbCaseDeplacement = $nb; }
}