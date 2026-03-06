<?php
declare(strict_types=1);

namespace App\Models\Classes;

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Joueur.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Pieces.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Cases.php';
require_once __DIR__.'/../enums/Formes.php';

use App\Models\Enums\Forme;
use App\Models\Classes\Joueur;
use App\Models\Classes\Piece;
use App\Models\Classes\Cases;

/**
 * Classe Plateau
 * @author Thomas   Boudeele    GrA
 * @author Louis    Hagues      GrA
 * @author Killian  Reine       GrA
 * 
 * Dernière modification : Vendredi 23 janvier 2026
 */

class Plateau
{
	/**
	 * Taille du plateau 6*6
	 */
	public const TAILLE = 6;

    /**
     * Représente le plateau de jeu.
     * Une case peut être vide (null) ou contenir une Piece.
     * @var array<int, array<int, Cases>>
     */
    private array $grille = [];

    private Jeu $jeu;

    /**
     * Constructeur : initialise une grille vide
     */
    public function __construct(Jeu $jeu){
        $this->jeu = $jeu;
        for ($ligne = 0; $ligne < self::TAILLE; $ligne++) {
            for ($colonne = 0; $colonne < self::TAILLE; $colonne++) {
                $this->grille[$ligne][$colonne] = new Cases($ligne, $colonne);
            }
        }
    }

    public function reset()
    {
        for ($ligne = 0; $ligne < self::TAILLE; $ligne++) {
            for ($colonne = 0; $colonne < self::TAILLE; $colonne++) {
                $this->grille[$ligne][$colonne] = new Cases($ligne, $colonne);
            }
        }
    }

	/**
	 * Permet de vérifier si la case étudiée est en dehors du plateau
	 * @param int $ligne le numéro de la ligne
	 * @param int $colonne le numéro de la colonne
	 * @return bool true si la case est en dehors du plateau, false sinon
	 */
	public function estDehors(int $ligne, int $colonne): bool
	{
		return ($ligne < 0 || $ligne >= self::TAILLE || $colonne < 0 || $colonne >= self::TAILLE);
	}

    /**
     *  Permet de récupérer la pièce aux positionx [x][y] de la grille
     * @return Case La pièce à la position (x,y) ou null si la case est vide ou en dehors du plateau
     */
    public function getCase(int $ligne, int $colonne): Cases
    {
        return $this->grille[$ligne][$colonne];
    }

    /**
     * Permet de placer une pièce à la position (x,y) de la grille
     * @throws InvalidArgumentException si la position est hors plateau
     * @throws RuntimeException si la case est déjà occupée
     * @param Piece $piece La pièce à placer
     * @param int $ligne La ligne où placer la pièce
     * @param int $colonne La colonne où placer la pièce
     * @return void
     */
    public function placerPiece(Piece $piece, int $ligne, int $colonne): void
    {
        if ($this->estDehors($ligne, $colonne)) {
            throw new \InvalidArgumentException('Position hors plateau.');
        }

        $case = $this->getCase($ligne, $colonne);

        if (!$case->estVide()) {
            throw new \RuntimeException('Case déjà occupée.');
        }

        $case->setPiece($piece);
    }

	/**
	 * Permet de retirer une pièce du plateau
	 * @param Piece $piece La pièce à retirer
	 * @return void
	 */
	public function retirerPiece(Piece $piece): void
	{
		$ligne = $piece->getLigne();
		$colonne = $piece->getColonne();

		if ($this->estDehors($ligne, $colonne)) {
			return;
		}

        $case = $this->getCase($ligne, $colonne);
        if ($case->getPiece() === $piece) {
            $case = $this->grille[$ligne][$colonne];
            $case->setPiece(null);
        }
    }

	/**
	 * Permet de mettre à jour le plateau en recalculant la portée de chaque pièce
	 * @return void
	 */
	public function recalculePortee(): void
	{
		for ($ligne = 0; $ligne < self::TAILLE; $ligne++) {
			for ($colonne = 0; $colonne < self::TAILLE; $colonne++) {
				$case = $this->grille[$ligne][$colonne];
                $p = $case->getPiece();
				if ($p !== null) {
					$p->recalculerPortee($this);
				}
			}
		}
	}

    /**
     * Getter de la grille
     * @return array<array<Cases>>
     */
    public function getGrille() : array
    {
        return $this->grille;
    }

    /**
     * Encode le plateau en JSON
     * @return string Représentation JSON du plateau
     */
    public function toJson(): string
    {
        $data = [];
        
        for ($ligne = 0; $ligne < self::TAILLE; $ligne++) {
            for ($colonne = 0; $colonne < self::TAILLE; $colonne++) {
                $case = $this->grille[$ligne][$colonne];
                $piece = $case->getPiece();
                
                if ($piece !== null) {
                    $data[] = [
                        'ligne' => $ligne,
                        'colonne' => $colonne,
                        'forme' => $piece->getForme()->value,
                        'couleur' => $piece->getJoueur()->getCouleur(),
                        'nbCaseDeplacement' => $piece->getNbCaseDeplacement()
                    ];
                }
            }
        }
        
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Reconstruit le plateau à partir d'un JSON
     * @param string $json Représentation JSON du plateau
     * @param Joueur $joueur1 Premier joueur
     * @param Joueur $joueur2 Deuxième joueur
     * @return void
     * @throws \InvalidArgumentException si le JSON est invalide
     */
    public function fromJson(string $json, Joueur $joueur1, Joueur $joueur2): void
    {
        $data = json_decode($json, true);
        
        if ($data === null) {
            throw new \InvalidArgumentException('JSON invalide');
        }
        
        // Réinitialiser le plateau
        $this->reset();
        
        // Recréer les pièces
        foreach ($data as $pieceData) {
            // Déterminer le joueur par la couleur
            $joueur = ($pieceData['couleur'] === 'noir') ? $joueur1 : $joueur2;
            $forme = Forme::from($pieceData['forme']);
            
            $piece = new Piece(
                $joueur,
                $forme,
                $pieceData['ligne'],
                $pieceData['colonne']
            );
            
            $piece->setNbCaseDeplacement($pieceData['nbCaseDeplacement']);
            
            $this->placerPiece($piece, $pieceData['ligne'], $pieceData['colonne']);
            
            // Ajouter la pièce au joueur
            $joueur->ajouterPiece($piece);
        }
        
        // Recalculer les portées
        $this->recalculePortee();
    }
}