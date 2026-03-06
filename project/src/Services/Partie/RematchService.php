<?php

namespace App\Services\Partie;

use App\Models\Classes\Jeu;
use App\Models\Classes\Joueur;
use App\Config\Database;

/**
 * Service de gestion des rematches
 * Responsable des invitations et acceptation/refus de rematch
 */
class RematchService
{
    private CodeManagementService $codeService;
    private GameCreationService $gameCreationService;

    public function __construct(
        CodeManagementService $codeService,
        GameCreationService $gameCreationService
    ) {
        $this->codeService = $codeService;
        $this->gameCreationService = $gameCreationService;
    }

    /**
     * Crée une invitation de rematch
     */
    public function createRematchInvitation(int $oldCode, int $hostUserId, int $invitedUserId): int
    {
        // Générer un nouveau code
        $newCode = $this->codeService->generateCode();

        // Récupérer les infos des utilisateurs
        $hostUser = Database::select("users", ['users_username'], ['users_id' => $hostUserId]);
        $invitedUser = Database::select("users", ['users_username'], ['users_id' => $invitedUserId]);

        if (empty($hostUser) || empty($invitedUser)) {
            throw new \Exception("Utilisateurs introuvables");
        }

        // Créer l'invitation
        Database::insert("rematch_invitations", [
            'code' => $newCode,
            'old_partie_code' => $oldCode,
            'host_user_id' => $hostUserId,
            'invited_user_id' => $invitedUserId,
            'host_username' => $hostUser[0]['users_username'],
            'invited_username' => $invitedUser[0]['users_username'],
            'status' => 'EN ATTENTE',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $newCode;
    }

    /**
     * Récupère une invitation de rematch
     */
    public function getRematchInvitation(int $code): ?array
    {
        $invitations = Database::select("rematch_invitations", ['*'], ['code' => $code]);
        return empty($invitations) ? null : $invitations[0];
    }

    /**
     * Vérifie si une invitation de rematch existe et est en attente pour un utilisateur
     */
    public function hasPendingRematchInvitation(int $userId): ?array
    {
        $invitations = Database::select("rematch_invitations", ['*'], [
            'invited_user_id' => $userId,
            'status' => 'EN ATTENTE'
        ]);

        if (empty($invitations)) {
            return null;
        }

        // Retourner la plus récente
        usort($invitations, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $invitations[0];
    }

    /**
     * Accepte une invitation de rematch
     */
    public function acceptRematchInvitation(int $code): bool
    {
        $invitation = $this->getRematchInvitation($code);
        if (!$invitation || $invitation['status'] !== 'EN ATTENTE') {
            return false;
        }

        // Mettre à jour le statut
        Database::update("rematch_invitations", [
            'status' => 'ACCEPTER',
            'accepted_at' => date('Y-m-d H:i:s')
        ], [
            'code' => $code
        ]);

        // Créer la partie
        $dateNow = date('Y-m-d H:i:s');

        $joueur1EloResult = Database::select('users', ['users_elo'], ['users_id' => $invitation['host_user_id']]);
        $joueur2EloResult = Database::select('users', ['users_elo'], ['users_id' => $invitation['invited_user_id']]);

        $joueur1Elo = (int)($joueur1EloResult[0]['users_elo'] ?? 0);
        $joueur2Elo = (int)($joueur2EloResult[0]['users_elo'] ?? 0);

        $joueur1 = new Joueur($invitation['host_username'], "noir", $joueur1Elo);
        $joueur2 = new Joueur($invitation['invited_username'], "rouge", $joueur2Elo);

        $jeu = new Jeu($joueur1, $code, $joueur2);

        // Insérer en base de données
        $this->gameCreationService->createGameInDatabase(
            $code,
            $jeu,
            (int)$invitation['host_user_id'],
            (int)$invitation['invited_user_id'],
            $dateNow
        );

        return true;
    }

    /**
     * Refuse une invitation de rematch
     */
    public function refuseRematchInvitation(int $code): bool
    {
        $invitation = $this->getRematchInvitation($code);
        if (!$invitation || $invitation['status'] !== 'EN ATTENTE') {
            return false;
        }

        Database::update("rematch_invitations", [
            'status' => 'REFUSER',
            'refused_at' => date('Y-m-d H:i:s')
        ], [
            'code' => $code
        ]);

        return true;
    }
}