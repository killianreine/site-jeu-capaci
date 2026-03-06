<?php

namespace App\Controllers\Partie;

use App\Services\PartieService;
use App\Models\Classes\Joueur;
use App\Config\Database;

/**
 * Controller des actions de parties
 * Responsable de rejoindre et annuler des parties
 */
class PartyActionController
{
    private PartieService $service;

    public function __construct(PartieService $service)
    {
        $this->service = $service;
    }

    /**
     * Rejoindre ou reprendre une partie avec un code
     */
    public function joinPartie(): void
    {
        $code = trim($_POST['code'] ?? '');

        if (empty($code)) {
            $_SESSION['error_message'] = "Veuillez entrer un code.";
            $this->redirect('/home');
            return;
        }

        if (!ctype_digit($code)) {
            $_SESSION['error_message'] = "Le code doit contenir uniquement des chiffres.";
            $this->redirect('/home');
            return;
        }

        $code = (int)$code;

        if ($code < 100000 || $code > 999999) {
            $_SESSION['error_message'] = "Le code doit comporter exactement 6 chiffres.";
            $this->redirect('/home');
            return;
        }

        $userId = (int)$_SESSION['users_id'];

        // La partie est déjà en cours 
        if ($this->service->partieExists($code)) {
            $gameState = $this->service->getGameState($code);

            if ($gameState && ($gameState['joueur1Id'] === $userId || $gameState['joueur2Id'] === $userId)) {
                // L'utilisateur fait bien partie des joueurs de la partie commencée
                $_SESSION['partie_code'] = $code;
                unset($_SESSION['waiting_for_player']);
                $_SESSION['success_message'] = "Partie reprise !";
                $this->redirect('/creer-partie');
                return;
            }

            $_SESSION['error_message'] = "Cette partie a déjà commencé et vous n'en faites pas partie.";
            $this->redirect('/home');
            return;
        }

        // La partie est en attente
        $waitingData = $this->service->findByCode($code);
        if (!$waitingData) {
            $_SESSION['error_message'] = "Code de partie introuvable. Vérifiez le code et réessayez.";
            $this->redirect('/home');
            return;
        }

        // L'hôte essaie de rejoindre sa partie
        if ((int)$waitingData['user_id'] === $userId) {
            $_SESSION['partie_code'] = $code;
            $_SESSION['waiting_for_player'] = true;
            $this->redirect('/creer-partie');
            return;
        }

        $users = Database::select("users", ['users_username', 'users_elo'], [
            'users_id' => $userId
        ]);
        if (empty($users)) {
            $_SESSION['error_message'] = "Erreur: Utilisateur introuvable";
            $this->redirect('/home');
            return;
        }

        $joueur2 = new Joueur($users[0]['users_username'], "rouge", $users[0]['users_elo']);
        $jeu = $this->service->joinGame($code, $joueur2);

        if (!$jeu) {
            $_SESSION['error_message'] = "Impossible de rejoindre la partie. Veuillez réessayer.";
            $this->redirect('/home');
            return;
        }

        $_SESSION['partie_code'] = $code;
        unset($_SESSION['waiting_for_player']);
        $_SESSION['success_message'] = "Vous avez rejoint la partie avec succès !";

        $this->redirect('/creer-partie');
    }

    /**
     * Annule une partie en attente
     */
    public function cancelPartie(): void
    {
        if (isset($_SESSION['partie_code'])) {
            $this->service->removeWaitingCode($_SESSION['partie_code']);
        }

        unset(
            $_SESSION['partie_code'],
            $_SESSION['waiting_for_player'],
            $_SESSION['waiting_for_rematch'],
            $_SESSION['rematch_wait_start'],
            $_SESSION['partie_is_public']
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

    /**
     * Alterne la visibilité (SANS JS)
     */
    public function toggleVisibility(): void
    {
        $code = (int)($_POST['code_partie'] ?? 0);
        $newStatus = isset($_POST['set_visibility']) ? (int)$_POST['set_visibility'] : 1;

        if ($code > 0) {
            $this->service->updateVisibility($code, (bool)$newStatus);
        }

        $this->redirect('/creer-partie');
    }
}