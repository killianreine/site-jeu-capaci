<?php
declare(strict_types=1);

/**
 * Classe Joueur
 * @author Thomas   Boudeele    GrA
 * @author Louis    Hagues      GrA
 * @author Killian  Reine       GrA
 * 
 * Dernière modification : Vendredi 23 janvier 2026
 */

namespace App\Models\Classes;


use App\Models\Enums\Forme;
use App\Models\Classes\Piece;

/**
 * Représente un joueur (UML: Joueur).
 */
final class Joueur
{
	private string $nom;

	/** @var string la couleur des pièces du joueur */
	private string $couleur;

	/** Compteurs demandés dans l'UML */
	public int $nbCiseau  = 0;
	public int $nbFeuille = 0;
	public int $nbPierre  = 0;

	public int $elo;

	private ?int $id = null;

	/** @var Piece[] */
	public array $pieces;
	public function __construct(string $nom, string $couleur,int $elo, ?int $id = null)
	{
		$this->nom = $nom;
		$this->couleur = $couleur;
		$this->pieces = [];
		$this->id = $id;
		$this->elo = $elo;
	}

	/**
	 * Ajoute une pièce au joueur et met à jour les compteurs.
	 */
	public function ajouterPiece(Piece $piece): void
	{
		match ($piece->getForme()) {
			Forme::CISEAUX  => $this->nbCiseau++,
			Forme::FEUILLES => $this->nbFeuille++,
			Forme::PIERRES  => $this->nbPierre++,
		};

		$this->pieces[] = $piece;
	}

	public function getPieces() { return $this->pieces; }

	/**
	 * Supprime (élimine) une pièce du joueur (UML: supprimer()).
	 */
   public function supprimer(Piece $piece): void
	{

		match ($piece->getForme()) {
			Forme::CISEAUX  => $this->nbCiseau--,
			Forme::FEUILLES => $this->nbFeuille--,
			Forme::PIERRES  => $this->nbPierre--,
		};

		foreach ($this->pieces as $index => $p) {
			if ($p->getColonne() === $piece->getColonne() && $p->getLigne() === $piece->getLigne()) 
			{
				unset($this->pieces[$index]);
				break;
			}
		}

		$this->pieces = array_values($this->pieces);
	}


	/**
	 * Condition de défaite 1 : plus aucune pièce d'une famille.
	 */
	public function aPerduUneFamille(): bool
	{
		return $this->nbCiseau <= 0 || $this->nbFeuille <= 0 || $this->nbPierre <= 0;
	}

	/**
	 * Remet les compteurs de familles à zéro.
	 * À appeler avant fromJson() lors du chargement depuis la BDD, pour éviter le doule comptage
	 */
	public function resetCompteurs(): void
	{
		$this->nbCiseau  = 0;
		$this->nbFeuille = 0;
		$this->nbPierre  = 0;
		$this->pieces    = [];
	}

	public function getNom(): string{return $this->nom;}
	public function getCouleur(): string{return $this->couleur;}
	public function getId(): int { return $this->id; }
	public function setId(int $id): void { $this->id = $id; }

	public function getNbPierre(){return $this->nbPierre;}
	public function getNbFeuille(){return $this->nbFeuille;}
	public function getNbCiseau() {return $this->nbCiseau;}

	public function getElo() {return $this->elo;}
	public function setElo($elo) {$this->elo = $elo;}

}