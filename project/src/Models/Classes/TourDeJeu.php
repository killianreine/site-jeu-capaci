<?php

namespace App\Models\Classes;

class TourDeJeu
{
    private int $id;
    private int $numeroTour;
    private int $joueurActif;

    private int $xDepart;
    private int $yDepart;

    private int $xArrive;
    private int $yArrive;

    private bool $aManger;

    public function __construct(
        int $id,
        int $numeroTour,
        int $joueurActif,
        int $xDepart,
        int $yDepart,
        int $xArrive,
        int $yArrive,
        bool $aManger
    ) {
        $this->id = $id;
        $this->numeroTour = $numeroTour;
        $this->joueurActif = $joueurActif;
        $this->xDepart = $xDepart;
        $this->yDepart = $yDepart;
        $this->xArrive = $xArrive;
        $this->yArrive = $yArrive;
        $this->aManger = $aManger;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroTour(): int
    {
        return $this->numeroTour;
    }

    public function getJoueurActif(): int
    {
        return $this->joueurActif;
    }

    public function getXDepart(): int
    {
        return $this->xDepart;
    }

    public function getYDepart(): int
    {
        return $this->yDepart;
    }

    public function getXArrive(): int
    {
        return $this->xArrive;
    }

    public function getYArrive(): int
    {
        return $this->yArrive;
    }

    public function aManger(): bool
    {
        return $this->aManger;
    }
}