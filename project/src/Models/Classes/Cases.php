<?php
declare(strict_types=1);

/**
 * Classe Case
 * @author Thomas   Boudeele    GrA
 * @author Louis    Hagues      GrA
 * @author Killian  Reine       GrA
 * 
 * Dernière modification : Vendredi 30 janvier 2026
 */
namespace App\Models\Classes;
use App\Models\Enums\Forme;
use App\Models\Classes\Piece;


/**
 * Représente un joueur (UML: Joueur).
 */
final class Cases
{
    /** @var int Position verticale (Y) */
	private int $ligne;

	/** @var int Position horizontale (X) */
	private int $colonne;

    /** @var Piece piece contenue dans la case : Piece|null */
    private Piece|null $piece;

    /** @var Forme le type de la pièce contenue dans la case */
   private ?Forme $forme = null;

    /**
     * Constructeur de la classe Case en fonction d'une pièce donnée
     */
    public function __construct(int $ligne, int $colonne){
        $this->ligne = $ligne;
        $this->colonne = $colonne;
        $this->piece = null;
    }

    /**
     * Permet de déterminer si la case est vide ou non
     */
    public function estVide() : bool{
        return $this->piece==null;
    }

    /**
     * Accesseur de la ligne
     * @return int
     */
    public function getLigne() : int{
        return $this->ligne;
    }

    /**
     * Accesseur de la colonne de la case
     * @return int
     */
    public function getColonne() : int{
        return $this->colonne;
    }

    /**
     * Accesseur de la pièce contenue dans la case
     * @return Piece     * @return Piece

     * @return Piece
     */
    public function getPiece() : ?Piece {
        return $this->piece;
    }

    public function setPiece(?Piece $piece) : void
    {
        if($piece != null){
            $this->piece = $piece;
            $this->forme = $piece->getForme();
            $piece->setLigne($this->ligne);
            $piece->setColonne($this->colonne);
        }
        else{
            $this->piece = null;
            $this->forme = null;
        }
    }

    public function getForme() : ?Forme{
        return $this->forme;
    }

    public function getJoueur() : Joueur{
        return $this->getPiece()->getJoueur();
    }
}