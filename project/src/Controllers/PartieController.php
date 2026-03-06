<?php

namespace App\Controllers;

use App\Services\PartieService;
use App\Middleware\AuthMiddleware;
use App\Controllers\Partie\DisplayController;
use App\Controllers\Partie\RematchController;
use App\Controllers\Partie\GameplayController;
use App\Controllers\Partie\PartyActionController;
use App\Controllers\Partie\AjaxController;

/**
 * Controller principal de gestion des parties
 */
class PartieController
{
    private PartieService $service;
    private DisplayController $displayController;
    private RematchController $rematchController;
    private GameplayController $gameplayController;
    private PartyActionController $partyActionController;
    private AjaxController $ajaxController;

    public function __construct()
    {
        $this->service = new PartieService();
        $this->displayController = new DisplayController($this->service);
        $this->rematchController = new RematchController($this->service);
        $this->gameplayController = new GameplayController($this->service);
        $this->partyActionController = new PartyActionController($this->service);
        $this->ajaxController = new AjaxController($this->service);
    }

    public function show(): void
    {
        AuthMiddleware::requireAuth();
        $this->displayController->show();
    }

    public function showWaitingRematch(): void
    {
        AuthMiddleware::requireAuth();
        $this->displayController->showWaitingRematch();
    }

    public function createRematch(): void
    {
        AuthMiddleware::requireAuth();
        $this->rematchController->createRematch();
    }

    public function acceptRematch(): void
    {
        AuthMiddleware::requireAuth();
        $this->rematchController->acceptRematch();
    }

    public function refuseRematch(): void
    {
        AuthMiddleware::requireAuth();
        $this->rematchController->refuseRematch();
    }

    public function recreatePartie(): void
    {
        AuthMiddleware::requireAuth();
        $this->rematchController->recreatePartie();
    }

    public function play(): void
    {
        AuthMiddleware::requireAuth();
        $this->gameplayController->play();
    }

    public function joinPartie(): void
    {
        AuthMiddleware::requireAuth();
        $this->partyActionController->joinPartie();
    }

    public function cancelPartie(): void
    {
        AuthMiddleware::requireAuth();
        $this->partyActionController->cancelPartie();
    }

    public function checkPlayerJoined(): void
    {
        AuthMiddleware::requireAuth();
        $this->ajaxController->checkPlayerJoined();
    }

    public function getGameState(): void
    {
        AuthMiddleware::requireAuth();
        $this->ajaxController->getGameState();
    }

    public function checkRematchStatus(): void
    {
        AuthMiddleware::requireAuth();
        $this->ajaxController->checkRematchStatus();
    }

    public function cancelRematch(): void
    {
        AuthMiddleware::requireAuth();
        $this->rematchController->cancelRematch();
    }

    public function toggleVisibility(): void
    {
        AuthMiddleware::requireAuth();
        $this->partyActionController->toggleVisibility();
    }

    /**
     * Quitte la partie en cours 
     */
    public function quitterPartie(): void
    {
        AuthMiddleware::requireAuth();
        unset(
            $_SESSION['partie_code'],
            $_SESSION['depart'],
            $_SESSION['joueur1_users_id'],
            $_SESSION['joueur2_users_id'],
            $_SESSION['joueur_couleur'],
            $_SESSION['waiting_for_player'],
            $_SESSION['waiting_for_rematch'],
            $_SESSION['rematch_wait_start'],
            $_SESSION['partie_is_public']
        );
        header('Location: ' . BASE_URL . '/home');
        exit;
    }
}