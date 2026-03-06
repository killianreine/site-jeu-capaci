<?php

namespace App\Controllers\Partie;

use App\Services\PartieService;

/**
 * Controller AJAX
 * Responsable des API JSON pour les mises à jour en temps réel
 */
class AjaxController
{
    private PartieService $service;

    public function __construct(PartieService $service)
    {
        $this->service = $service;
    }

    /**
     * Vérifie si un joueur a rejoint la partie
     */
    public function checkPlayerJoined(): void
    {
        header('Content-Type: application/json');

        $code = $_SESSION['partie_code'] ?? null;
        
        if (!$code) {
            echo json_encode([
                'error' => true, 
                'message' => 'Aucun code de partie en session'
            ]);
            exit;
        }

        if ($this->service->partieExists($code)) {
            unset($_SESSION['waiting_for_player']);
            echo json_encode(['joined' => true]);
        } else {
            echo json_encode(['joined' => false]);
        }
        exit;
    }

    /**
     * Récupère l'état complet de la partie
     */
    public function getGameState(): void
    {
        header('Content-Type: application/json');

        $code = $_SESSION['partie_code'] ?? null;
        
        if (!$code) {
            echo json_encode([
                'error' => true, 
                'message' => 'Aucun code de partie'
            ]);
            exit;
        }

        $state = $this->service->getGameState($code);
        
        if (!$state) {
            echo json_encode([
                'error' => true, 
                'message' => 'Partie introuvable'
            ]);
            exit;
        }

        $currentUserId = (int)$_SESSION['users_id'];
        $isMyTurn = ((int)$state['joueurActifId'] === $currentUserId);

        echo json_encode([
            'error' => false,
            'plateau' => $state['plateau'],
            'isMyTurn' => $isMyTurn,
            'joueurActifId' => $state['joueurActifId'],
            'etat' => $state['etat'],
            'joueur1Name' => $state['joueur1Name'],
            'joueur2Name' => $state['joueur2Name'],
            'dateModif' => $state['dateModif'],
            'gagnantId' => $state['gagnantId'] ?? null
        ]);
        exit;
    }

    /**
     * Vérifie le statut d'une invitation de rematch
     */
    public function checkRematchStatus(): void
    {
        header('Content-Type: application/json');

        $code = $_SESSION['partie_code'] ?? null;
        if (!$code) {
            echo json_encode(['error' => true, 'message' => 'Aucun code']);
            exit;
        }

        $waitStart = $_SESSION['rematch_wait_start'] ?? time();
        $waitTime = time() - $waitStart;
        
        if ($waitTime > 300) {
            unset($_SESSION['waiting_for_rematch']);
            unset($_SESSION['partie_code']);
            unset($_SESSION['rematch_wait_start']);
            echo json_encode([
                'error' => false,
                'status' => 'TIMEOUT',
                'message' => "L'attente a expiré"
            ]);
            exit;
        }

        $invitation = $this->service->getRematchInvitation($code);
        if (!$invitation) {
            echo json_encode(['error' => true, 'message' => 'Invitation introuvable']);
            exit;
        }

        echo json_encode([
            'error' => false,
            'status' => $invitation['status'],
            'timestamp' => time(),
            'waitTime' => $waitTime
        ]);
        exit;
    }
}