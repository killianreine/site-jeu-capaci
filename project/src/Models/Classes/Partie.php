<?php

namespace App\Models\Classes;

use App\Models\Classes\Joueur;

/**
 * Classe permettant de représenter une partie
 * Représentation d'un enregistrement de la table parties
 */
class Partie
{
    /**
     * @var int identifiant de la partie dans la bado
     */
    private int $id_partie;

    /**
     * @var string code d'accès (permettant de rejoindre la partie)
     */
    private string $code_partie;

    /**
     * @var Joueur host de la partie
     */
    private Joueur $joueur1;

    /**
     * @var Joueur|null second joueur de la partie (celui qui rejoint)
     */
    private ?Joueur $joueur2;

    /**
     * @var string statut de la partie (en_attente, en_cours, terminee)
     */
    private string $etat_partie;

    /**
     * @var bool indique si la partie est publique ou privée (rejoignable via code)
     */
    private bool $publique;

    public function getIdPartie(): int { return $this->id_partie; }

    public function getCodePartie(): string { return $this->code_partie; }

    public function getJoueur1(): Joueur { return $this->joueur1; }

    public function getJoueur2(): ?Joueur { return $this->joueur2; }

    public function getEtatPartie(): string { return $this->etat_partie; }

    public function setIdPartie(int $id_partie): void { $this->id_partie = $id_partie; }

    public function setCodePartie(string $code_partie): void { $this->code_partie = $code_partie; }

    public function setJoueur1(Joueur $joueur1): void { $this->joueur1 = $joueur1; }

    public function setJoueur2(?Joueur $joueur2): void { $this->joueur2 = $joueur2; }

    public function setEtatPartie(string $etat_partie): void { $this->etat_partie = $etat_partie; }

    public function isPublique(): bool { return $this->publique; }

    public function setPublique(bool $publique): void { $this->publique = $publique; }

    /**
     * Convertit la partie en tableau associatif (pour save dans la bado)
     */
    public function toArray(): array
    {
        return [
            'partie_id' => $this->id_partie ?? null,
            'partie_code' => $this->code_partie,
            'partie_joueur1' => $this->joueur1->getId(),
            'partie_joueur2' => $this->joueur2 ? $this->joueur2->getId() : null,
            'partie_etat' => $this->etat_partie,
            'partie_publique' => $this->publique ? 1 : 0
        ];
    }
}