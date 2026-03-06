<?php

namespace App\Controllers\Partie;

use App\Core\View;
use App\Services\PartieService;
use App\Models\Classes\Jeu;
use App\Models\Classes\Joueur;
use App\Config\Database;

/**
 * Controller d'affichage des parties
 * Responsable du rendu des vues de parties
 */
class DisplayController
{
    private PartieService $service;

    public function __construct(PartieService $service)
    {
        $this->service = $service;
    }

    /**
     * Affiche la page principale de la partie
     */
    public function show(): void
    {
        $code = $_SESSION['partie_code'] ?? null;
        $waitingForPlayer = false;
        $jeu = null;
        $isPublic = false; // Privée par défaut

        // Vérifier s'il y a une invitation de rematch en attente
        $rematchInvitation = $this->service->hasPendingRematchInvitation($_SESSION['users_id']);
        if ($rematchInvitation) {
            $this->showRematchInvitation($rematchInvitation);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type_partie'])) {
            if ($code) {
                $this->service->removeWaitingCode((int)$code);
            }
            unset(
                $_SESSION['partie_code'],
                $_SESSION['waiting_for_player'],
                $_SESSION['partie_is_public']
            );
            $code = null;
        }

        if ($code) {
            if ($this->service->partieExists($code)) {
                $jeu = $this->service->loadGameFromDB($code);
                $waitingForPlayer = false;
                unset($_SESSION['waiting_for_player']);
            } else {
                $waitingForPlayer = true;
                $isPublic = $_SESSION['partie_is_public'] ?? false;
            }
        }

        if (!$jeu && !$waitingForPlayer) {
            $isPublic = isset($_POST['type_partie']) && $_POST['type_partie'] === 'publique';

            $jeu = $this->service->createGame();
            $code = $jeu->getCode(); 
            $_SESSION['partie_code'] = $code;
            $_SESSION['waiting_for_player'] = true;
            $_SESSION['partie_is_public'] = $isPublic;
            $waitingForPlayer = true;

            $users = Database::select("users", ['users_id', 'users_username'], [
                'users_id' => $_SESSION['users_id']
            ]);

            if (!empty($users)) {
                $this->service->insertWaitingCode(
                    $code,
                    $users[0]['users_id'],
                    $users[0]['users_username'],
                    $isPublic // true = publique, false = privée
                );
            }
        }

        if ($waitingForPlayer) {
            $users = Database::select("users", ['users_username', 'users_elo'], [
                'users_id' => $_SESSION['users_id']
            ]);
            $username = $users[0]['users_username'] ?? 'Joueur 1';
            $joueur1 = new Joueur($username, "noir", $users[0]['users_elo']);
            $jeu = new Jeu($joueur1, $code ?? 0);
        }

        $gameData = $this->getGameOverData();
        $this->clearGameOverSession();

        $isMyTurn = false;
        $currentUserId = $_SESSION['users_id'];
        $joueur2Name = null;
        $gameState = null;

        if (!$waitingForPlayer && $code) {
            $gameState = $this->service->getGameState($code);
            if ($gameState) {
                $isMyTurn = ((int)$gameState['joueurActifId'] === (int)$currentUserId);
                $joueur2Name = $gameState['joueur2Name'];

                if ($gameState['etat'] === 'TERMINEE' && !$gameData['gameOver']) {
                    $gameData = $this->loadGameOverData($gameState);
                }

                if ($gameState['etat'] !== 'TERMINEE' && !$gameData['gameOver'] && $jeu) {
                    $vainqueur = $jeu->verifVictoire();
                    if ($vainqueur !== null) {
                        if ($vainqueur === 'J1') {
                            $gagnantNom = $jeu->getJoueur1()->getNom();
                            $perdant    = $jeu->getJoueur2();
                        } else {
                            $gagnantNom = $jeu->getJoueur2()->getNom();
                            $perdant    = $jeu->getJoueur1();
                        }

                        if ($perdant->aPerduUneFamille()) {
                            if ($perdant->nbPierre <= 0)      $raison = "a perdu toutes ses pierres";
                            elseif ($perdant->nbFeuille <= 0) $raison = "a perdu toutes ses feuilles";
                            else                              $raison = "a perdu tous ses ciseaux";
                        } else {
                            $raison = "ne peut plus effectuer de mouvement";
                        }

                        $this->service->endGame($code, $gagnantNom);
                        $_SESSION['GAME_OVER'] = [true, $raison];
                        $_SESSION['GAGNANT']   = $gagnantNom;
                        $_SESSION['PERDANT']   = $perdant->getNom();
                        $gameData = [
                            'gameOver' => [true, $raison],
                            'gagnant'  => $gagnantNom,
                            'perdant'  => $perdant->getNom(),
                        ];
                    }
                }
            }
        }

        View::render('partie/index', [
            'jeu'              => $jeu,
            'gameOver'         => $gameData['gameOver'],
            'gagnant'          => $gameData['gagnant'],
            'perdant'          => $gameData['perdant'],
            'waitingForPlayer' => $waitingForPlayer,
            'isPublic'         => $isPublic,
            'codePartie'       => $code,
            'joueur1'          => $jeu->getJoueur1(),
            'joueur2'          => $jeu->getJoueur2(),
            'isConnected'      => true,
            'isMyTurn'         => $isMyTurn,
            'currentUserId'    => $currentUserId,
            'gameState'        => $gameState,
            'styles'           => [
                'components/cases.css',
                'components/plateau.css',
                'components/modal.css',
                'components/waiting_modal.css',
                'components/modal_join_game.css',
                'pages/home.css',
                'pages/game.css',
                'pages/indexPartie.css'
            ]
        ]);
    }

    public function showRematchInvitation(array $invitation): void
    {
        View::render('partie/rematch_invitation', [
            'invitation' => $invitation,
            'isConnected' => true,
            'styles' => [
                'components/modal.css',
                'pages/home.css',
                'pages/indexPartie.css'
            ]
        ]);
    }

    public function showWaitingRematch(): void
    {
        $code = $_SESSION['partie_code'] ?? null;
        if (!$code) {
            $this->redirect('/home');
            return;
        }

        $invitation = $this->service->getRematchInvitation($code);
        
        if (!$invitation) {
            unset($_SESSION['waiting_for_rematch'], $_SESSION['partie_code'], $_SESSION['rematch_wait_start']);
            $_SESSION['error_message'] = "Invitation introuvable.";
            $this->redirect('/home');
            return;
        }
        
        if ($invitation['status'] === 'ACCEPTER') {
            unset($_SESSION['waiting_for_rematch'], $_SESSION['rematch_wait_start']);
            $_SESSION['success_message'] = "✅ Invitation acceptée ! La partie commence.";
            $this->redirect('/creer-partie');
            return;
        }
        
        if ($invitation['status'] === 'REFUSER') {
            unset($_SESSION['waiting_for_rematch'], $_SESSION['partie_code'], $_SESSION['rematch_wait_start']);
            $_SESSION['error_message'] = "❌ L'invitation a été refusée.";
            $this->redirect('/home');
            return;
        }
        
        if (!isset($_SESSION['rematch_wait_start'])) {
            $_SESSION['rematch_wait_start'] = time();
        }
        
        $waitTime = time() - $_SESSION['rematch_wait_start'];
        
        if ($waitTime > 300) {
            unset($_SESSION['waiting_for_rematch'], $_SESSION['partie_code'], $_SESSION['rematch_wait_start']);
            $_SESSION['error_message'] = "⏱️ L'attente a expiré.";
            $this->redirect('/home');
            return;
        }

        View::render('partie/waiting_rematch', [
            'invitation' => $invitation,
            'codePartie' => $code,
            'isConnected' => true,
            'waitTime' => $waitTime,
            'styles' => [
                'components/modal.css',
                'pages/home.css',
                'pages/indexPartie.css'
            ]
        ]);
    }

    private function loadGameOverData(array $gameState): array
    {
        $gagnantId = $gameState['gagnantId'] ?? null;
        if (!$gagnantId) return ['gameOver' => null, 'gagnant' => null, 'perdant' => null];

        $gagnantName = ((int)$gagnantId === (int)$gameState['joueur1Id']) ? $gameState['joueur1Name'] : $gameState['joueur2Name'];
        $perdantName = ((int)$gagnantId === (int)$gameState['joueur1Id']) ? $gameState['joueur2Name'] : $gameState['joueur1Name'];
        $raison = $_SESSION['GAME_OVER'][1] ?? "a perdu une famille";
        
        return [
            'gameOver' => [true, $raison],
            'gagnant'  => $gagnantName,
            'perdant'  => $perdantName
        ];
    }

    private function getGameOverData(): array
    {
        return [
            'gameOver' => $_SESSION['GAME_OVER'] ?? null,
            'gagnant'  => $_SESSION['GAGNANT']   ?? null,
            'perdant'  => $_SESSION['PERDANT']   ?? null,
        ];
    }

    private function clearGameOverSession(): void
    {
        unset($_SESSION['GAME_OVER'], $_SESSION['GAGNANT'], $_SESSION['PERDANT']);
    }

    private function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}