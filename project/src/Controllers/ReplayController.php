<?php

namespace App\Controllers;

use App\Core\View;
use App\Middleware\AuthMiddleware;
use App\Services\ReplayService;
use App\Services\PartieService;

class ReplayController
{
	public const PREC = 0;
	public const SUIVANT = 1;

	private int $nbTour;
	private $service;
	private $parties;
	private $id;

	private $username1;
	private $username2;

	public function __construct($id)
	{
		$this->nbTour = 0;
		$this->id = $id;
		
		$players = ReplayService::getPlayers($id);

		$this->username1 = $players[0]['username1'];
		$this->username2 = $players[0]['username2'];

		$this->service = new ReplayService($this->username1,$this->username2 );

		$this->parties = $this->service->getAllTour($id);
	}

	public function show()
	{
		$isConnected = AuthMiddleware::isConnect();
		$joueur = AuthMiddleware::getConnectdUser();

		$userId = is_array($joueur) ? (int)$joueur['users_id'] : (int)$joueur;

		if ($isConnected && $userId) {

			$tourIndex = isset($_GET['tour']) ? (int)$_GET['tour'] : -1;

			if ($tourIndex >= 0 && isset($this->parties[$tourIndex])) {

				$tour = $this->parties[$tourIndex];

				$plateau = $this->service->reconstruirePlateau($this->parties, $tourIndex);

				$caseDeplacer = [$tour->getXArrive(), $tour->getYArrive()];
				$aManger = $tour->aManger();

			}
			else {

				// plateau de départ
				$plateau = $this->service->createReplayBoard();

				$caseDeplacer = -1;
				$aManger = false;
			}
			View::render('replay/index', [
				'title' => 'Replay',
				'isConnected' => $isConnected,
				'plateau' => $plateau,
				'tour' => $tourIndex,
				'partie_id' => $this->id,
				'case' => $caseDeplacer,
				'username1' => $this->username1,
				'username2' => $this->username2,
				'aManger' => $aManger,
				'styles' => [
					'components/cases.css',
					'components/plateau.css',
					'components/modal.css',
					'components/waiting_modal.css',
					'components/modal_join_game.css',
					'pages/replay.css',
					'pages/home.css'
				]
			]);
		}
	}

	public function action(): void
	{
		$action   = (int)($_POST['action'] ?? null);
		$partieId = (int)($_POST['partie_id'] ?? 0);
		$tour     = (int)($_POST['tour'] ?? 0);

		if ($action === self::PREC) {
			$tour = max(-1, $tour - 1);
		}

		$maxTour = count($this->parties);

		if ($action === self::SUIVANT) {
			$tour = min($maxTour, $tour + 1);
		}

		header("Location: " . BASE_URL . "/partie/$partieId?tour=$tour");
		exit;
	}
}
