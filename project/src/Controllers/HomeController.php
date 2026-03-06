<?php

namespace App\Controllers;

use App\Core\View;
use App\Middleware\AuthMiddleware;
use App\Services\PartieService;

class HomeController
{
	public static function index()
	{
		$isConnected = AuthMiddleware::isConnect();
		$joueur = AuthMiddleware::getConnectdUser();

		$userId = is_array($joueur) ? (int)$joueur['users_id'] : (int)$joueur;

		$parties = [];
		if ($isConnected && $userId) {
    	$partieService = new PartieService();
    	$parties = $partieService->getAvailableGames($userId);
	}

		View::render('home/index', [
			'title' => 'Accueil',
			'isConnected' => $isConnected,
			'joueur' => $joueur,
			'parties'     => $parties,
			'success' => $_SESSION['success_message'] ?? null,
			'styles' => [
				'/components/button.css',
				'pages/home.css', 
				'/components/modal_join_game.css', 
				'/components/modal.css',
				'/components/table.css',
				
			]
		]);

		unset($_SESSION['success_message']);
	}
}