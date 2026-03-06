<?php

namespace App\Services;

use App\Models\Classes\Jeu;
use App\Models\Classes\Joueur;
use App\Services\Partie\CodeManagementService;
use App\Services\Partie\GameCreationService;
use App\Services\Partie\GamePersistenceService;
use App\Services\Partie\GameStateService;
use App\Services\Partie\RematchService; 
use App\Config\Database;

/**
 * Service principal de gestion des parties
 */
class PartieService
{
    private CodeManagementService $codeService;
    private GameCreationService $creationService;
    private GamePersistenceService $persistenceService;
    private GameStateService $stateService;
    private RematchService $rematchService;

    public function __construct()
    {
        $this->codeService = new CodeManagementService();
        $this->creationService = new GameCreationService($this->codeService);
        $this->persistenceService = new GamePersistenceService();
        $this->stateService = new GameStateService();
        $this->rematchService = new RematchService(
            $this->codeService,
            $this->creationService
        );
    }

    /**
     * Crée une nouvelle partie
     */
    public function createGame(): Jeu
    {
        
        return $this->creationService->createGame();
    }

    /**
     * Rejoindre une partie existante
     */
    public function joinGame(int $code, Joueur $joueur): ?Jeu
    {
        return $this->creationService->joinGame($code, $joueur);
    }

    /**
     * Récupère les données d'un code d'attente
     */
    public function findByCode(int $code): ?array
    {
        return $this->codeService->findByCode($code);
    }

    /**
     * Insère un code d'attente
     */
    public function insertWaitingCode(int $code, int $userId, string $username, bool $isPublic = false): void
    {
        $this->codeService->insertWaitingCode($code, $userId, $username, $isPublic);
    }

    /**
     * Supprime un code d'attente
     */
    public function removeWaitingCode(int $code): void
    {
        $this->codeService->removeWaitingCode($code);
    }

    /**
     * Crée une invitation de rematch
     */
    public function createRematchInvitation(int $oldCode, int $hostUserId, int $invitedUserId): int
    {
        return $this->rematchService->createRematchInvitation($oldCode, $hostUserId, $invitedUserId);
    }

    /**
     * Récupère une invitation de rematch
     */
    public function getRematchInvitation(int $code): ?array
    {
        return $this->rematchService->getRematchInvitation($code);
    }

    /**
     * Accepte une invitation de rematch
     */
    public function acceptRematchInvitation(int $code): bool
    {
        return $this->rematchService->acceptRematchInvitation($code);
    }

    /**
     * Refuse une invitation de rematch
     */
    public function refuseRematchInvitation(int $code): bool
    {
        return $this->rematchService->refuseRematchInvitation($code);
    }

    /**
     * Vérifie si un utilisateur a une invitation de rematch en attente
     */
    public function hasPendingRematchInvitation(int $userId): ?array
    {
        return $this->rematchService->hasPendingRematchInvitation($userId);
    }

    /**
     * Récupère l'état complet d'une partie
     */
    public function getGameState(int $code): ?array
    {
        return $this->stateService->getGameState($code);
    }

    /**
     * Vérifie si c'est le tour d'un joueur
     */
    public function isPlayerTurn(int $code, int $userId): bool
    {
        return $this->stateService->isPlayerTurn($code, $userId);
    }

    /**
     * Vérifie si une partie existe
     */
    public function partieExists(int $code): bool
    {
        return $this->stateService->partieExists($code);
    }

    /**
     * Charge une partie depuis la base de données
     */
    public function loadGameFromDB(int $code): ?Jeu
    {
        return $this->persistenceService->loadGameFromDB($code);
    }

    /**
     * Sauvegarde une partie
     */
    public function saveGame(Jeu $jeu): void
    {
        $this->persistenceService->saveGame($jeu);
    }

    /**
     * Termine une partie et enregistre le gagnant
     */
    public function endGame(int $code, string $gagnantUsername): void
    {
        $this->persistenceService->endGame($code, $gagnantUsername);
    }

    /**
     * Sauvegarde le coup joué dans la base de données
     */
    public function savePlay(int $nTour, Joueur $joueurActif, int $xdepart, int $ydepart, int $xarrivee, int $yarrivee, bool $aManger, int $partieCode): void
    {
        $partieId = Database::select('partie', ['partie_id'], ['partie_code' => $partieCode])[0]['partie_id'];


        $playData = [
            'tdj_nTour' => $nTour,
            'tdj_joueurActif' => $joueurActif->getId(),
            'xDepart' => $xdepart,
            'yDepart' => $ydepart,
            'xArrive' => $xarrivee,
            'yArrive' => $yarrivee,
            'tdj_aManger' => $aManger ? 1 : 0,
            'partie_id' => $partieId
        ];
        Database::insert('tourdejeu', $playData);
    }

    /**
     * Liste des parties qu'un utilisateur peut voir/rejoindre
     */
    public function getAvailableGames(int $userId): array
    {
        return $this->persistenceService->getVisibleGamesForUser($userId);
    }

    public function toggleVisibility(): void 
    {
        $code = $_POST['code_partie'] ?? null;
        
        // On récupère la valeur directe (0 ou 1) envoyée par le bouton 'set_visibility'
        $newStatus = isset($_POST['set_visibility']) ? (bool)$_POST['set_visibility'] : true; 

        if ($code) {
            $this->updateVisibility((int)$code, $newStatus);
        }

        // Redirection vers la page actuelle pour voir le changement
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/partie'))); 
        exit;
    }

    public function updateVisibility(int $code, bool $isPublic): void
    {
        $this->codeService->updateVisibility($code, $isPublic);
    }
}