<?php

namespace App\Controllers\Partie;

use App\Services\PartieService;
use App\Models\Classes\Jeu;
use App\Models\Classes\Joueur;

/**
 * Controller de gameplay
 */
class GameplayController
{
    private PartieService $service;

    public function __construct(PartieService $service)
    {
        $this->service = $service;
    }

    public function play(): void
    {
        $code = $_SESSION['partie_code'] ?? null;
        if (!$code) {
            $this->setError("Aucune partie en cours");
            $this->redirect('/home');
            return;
        }

        $jeu = $this->service->loadGameFromDB($code);
        if (!$jeu) {
            $this->setError("Impossible de charger la partie");
            $this->redirect('/home');
            return;
        }

        $state = $this->service->getGameState($code);
        if ($state && $state['etat'] === 'TERMINEE') {
            $this->setError("Cette partie est terminée");
            $this->redirect('/creer-partie');
            return;
        }

        $currentUserId = $_SESSION['users_id'];
        if (!$this->service->isPlayerTurn($code, $currentUserId)) {
            $this->setError("Ce n'est pas votre tour !");
            $this->redirect('/creer-partie');
            return;
        }

        $coordinates = $this->parseCoordinates();
        if (!$coordinates) {
            $this->setError("Coordonnées invalides");
            $this->redirect('/creer-partie');
            return;
        }

        [$ligne, $colonne] = $coordinates;

        if (!$this->hasPieceSelected()) {
            $this->selectPiece($ligne, $colonne);
            return;
        }

        $this->handleMove($jeu, $ligne, $colonne);
    }

    private function selectPiece(int $ligne, int $colonne): void
    {
        $_SESSION['depart'] = ['ligne' => $ligne, 'colonne' => $colonne];
        $this->redirect('/creer-partie');
    }

    private function cancelSelection(): void
    {
        unset($_SESSION['depart']);
        $this->redirect('/creer-partie');
    }

    private function hasPieceSelected(): bool
    {
        return isset($_SESSION['depart']);
    }

    private function getSelectedPosition(): ?array
    {
        return $_SESSION['depart'] ?? null;
    }

    private function handleMove(Jeu $jeu, int $ligne, int $colonne): void
    {
        $depart = $this->getSelectedPosition();

        if ($this->isSamePosition($depart, $ligne, $colonne)) {
            $this->cancelSelection();
            return;
        }

        $piece = $this->getPieceAt($jeu, $depart['ligne'], $depart['colonne']);
        if (!$piece) {
            $this->setError("Aucune pièce sélectionnée.");
            $this->cancelSelection();
            return;
        }

        $this->attemptMove($jeu, $piece, $ligne, $colonne);
    }

    private function attemptMove(Jeu $jeu, $piece, int $ligne, int $colonne): void
    {
        $resultat = $jeu->jouerCoup($piece, $ligne, $colonne);

        if ($resultat !== 'OK') {
            $this->handleMoveError($resultat);
            $this->cancelSelection();
            return;
        }

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
                if ($perdant->nbPierre <= 0)       $raison = "a perdu toutes ses pierres";
                elseif ($perdant->nbFeuille <= 0)  $raison = "a perdu toutes ses feuilles";
                else                               $raison = "a perdu tous ses ciseaux";
            } else {
                $raison = "ne peut plus effectuer de mouvement";
            }

            $this->service->endGame($jeu->getCode(), $gagnantNom);
            $this->setGameOver($gagnantNom, $perdant->getNom(), $raison);
        }

        $this->service->saveGame($jeu);
        $this->cancelSelection();
    }

    private function handleMoveError(string $errorCode): void
    {
        $messages = [
            'BAD_PLAYER'   => "Ce n'est pas à votre tour de jouer",
            'BADPLAYER'    => "Ce n'est pas à votre tour de jouer",
            'INVALID_MOVE' => "Déplacement invalide",
            'BLOCKED_MOVE' => "Cette pièce ne peut pas capturer celle-ci"
        ];
        $this->setError($messages[$errorCode] ?? "Erreur : $errorCode");
    }

    private function setGameOver(string $gagnant, string $perdant, string $raison): void
    {
        $_SESSION['GAME_OVER'] = [true, $raison];
        $_SESSION['GAGNANT']   = $gagnant;
        $_SESSION['PERDANT']   = $perdant;
    }

    private function parseCoordinates(): ?array
    {
        $case = $_POST['case'] ?? null;
        if (!$case || !str_contains($case, ',')) return null;
        [$ligne, $colonne] = explode(',', $case);
        return [(int)$ligne, (int)$colonne];
    }

    private function getPieceAt(Jeu $jeu, int $ligne, int $colonne)
    {
        return $jeu->get_plateau()->getCase($ligne, $colonne)->getPiece();
    }

    private function isSamePosition(array $pos, int $ligne, int $colonne): bool
    {
        return $pos['ligne'] === $ligne && $pos['colonne'] === $colonne;
    }

    private function setError(string $message): void
    {
        $_SESSION['MODAL_MESSAGE'] = $message;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}