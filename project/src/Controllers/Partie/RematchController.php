<?php

namespace App\Controllers\Partie;

use App\Services\PartieService;

/**
 * Controller de gestion des rematches
 * Responsable des invitations et réponses de rematch
 */
class RematchController
{
    private PartieService $service;

    public function __construct(PartieService $service)
    {
        $this->service = $service;
    }

    /**
     * Crée une invitation de rematch
     */
    public function createRematch(): void
    {
        $code = $_SESSION['partie_code'] ?? null;
        if (!$code) {
            $_SESSION['error_message'] = "Aucune partie en cours";
            $this->redirect('/home');
            return;
        }

        $state = $this->service->getGameState($code);
        if (!$state || $state['etat'] !== 'TERMINEE') {
            $_SESSION['error_message'] = "Cette partie n'est pas terminée.";
            $this->redirect('/creer-partie');
            return;
        }

        $currentUserId = (int)$_SESSION['users_id'];
        
        if ($currentUserId !== $state['joueur1Id'] && $currentUserId !== $state['joueur2Id']) {
            $_SESSION['error_message'] = "Vous ne faites pas partie de cette partie.";
            $this->redirect('/home');
            return;
        }

        $adversaireId = ($currentUserId === $state['joueur1Id']) 
            ? $state['joueur2Id'] 
            : $state['joueur1Id'];

        try {
            $newCode = $this->service->createRematchInvitation($code, $currentUserId, $adversaireId);
            
            $_SESSION['partie_code'] = $newCode;
            $_SESSION['waiting_for_rematch'] = true;
            $_SESSION['rematch_wait_start'] = time();
            $_SESSION['success_message'] = "Invitation envoyée ! En attente de la réponse...";
            
            $this->redirect('/attente-rematch');
        } catch (\Exception $e) {
            $_SESSION['error_message'] = "Erreur lors de la création de l'invitation: " . $e->getMessage();
            $this->redirect('/creer-partie');
        }
    }

    /**
     * Accepte une invitation de rematch
     */
    public function acceptRematch(): void
    {
        $code = (int)($_POST['code'] ?? 0);
        if (!$code) {
            $_SESSION['error_message'] = "Code invalide";
            $this->redirect('/home');
            return;
        }

        $invitation = $this->service->getRematchInvitation($code);
        if (!$invitation) {
            $_SESSION['error_message'] = "Invitation introuvable";
            $this->redirect('/home');
            return;
        }

        if ((int)$invitation['invited_user_id'] !== (int)$_SESSION['users_id']) {
            $_SESSION['error_message'] = "Cette invitation ne vous est pas destinée";
            $this->redirect('/home');
            return;
        }

        if ($this->service->acceptRematchInvitation($code)) {
            $_SESSION['partie_code'] = $code;
            unset($_SESSION['waiting_for_player']);
            unset($_SESSION['waiting_for_rematch']);
            $_SESSION['success_message'] = "✅ Invitation acceptée ! Bonne partie !";
            
            $this->redirect('/creer-partie');
        } else {
            $_SESSION['error_message'] = "Erreur lors de l'acceptation";
            $this->redirect('/home');
        }
    }

    /**
     * Refuse une invitation de rematch
     */
    public function refuseRematch(): void
    {
        $code = (int)($_POST['code'] ?? 0);
        if (!$code) {
            $_SESSION['error_message'] = "Code invalide";
            $this->redirect('/home');
            return;
        }

        $invitation = $this->service->getRematchInvitation($code);
        if (!$invitation) {
            $_SESSION['error_message'] = "Invitation introuvable";
            $this->redirect('/home');
            return;
        }

        if ((int)$invitation['invited_user_id'] !== (int)$_SESSION['users_id']) {
            $_SESSION['error_message'] = "Cette invitation ne vous est pas destinée";
            $this->redirect('/home');
            return;
        }

        if ($this->service->refuseRematchInvitation($code)) {
            $_SESSION['success_message'] = "Invitation refusée";
            $this->redirect('/home');
        } else {
            $_SESSION['error_message'] = "Erreur lors du refus";
            $this->redirect('/home');
        }
    }

    /**
     * Recrée une partie (alias de createRematch pour compatibilité)
     */
    public function recreatePartie(): void
    {
        $this->createRematch();
    }

    /**
     * Annule une invitation de rematch envoyée par l'hôte
     */
    public function cancelRematch(): void
    {
        $code = $_SESSION['partie_code'] ?? null;

        if ($code) {
            $this->service->refuseRematchInvitation((int)$code);
        }

        unset(
            $_SESSION['partie_code'],
            $_SESSION['waiting_for_rematch'],
            $_SESSION['rematch_wait_start']
        );

        $_SESSION['info_message'] = "Invitation annulée.";
        $this->redirect('/home');
    }

    /**
     * Redirige vers un chemin
     */
    private function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}