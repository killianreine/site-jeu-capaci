<?php

namespace App\Controllers;

use App\Config\Database;
use App\Core\View;
use App\Models\Classes\Joueur;
use App\Services\AuthService;
use App\Middleware\AuthMiddleware;

class AuthController
{
	/**
	 * Affiche le formulaire de connexion
	 */
	public function showLogin(): void
	{
		AuthMiddleware::redirectIfAuthenticated();

		View::render('auth/login', [
			'title' => 'Connexion',
			'isConnected' => false,
			'error' => $_SESSION['error_message'] ?? null,
			'styles' => ['pages/auth.css']
		]);

		unset($_SESSION['error_message']);
	}

	/**
	 * Traite la connexion
	 */
	public function login(): void
	{
		// $nom = trim($_POST['nom'] ?? '');
		// $couleur = $_POST['couleur'] ?? 'noir';

		// if (empty($nom)) {
		// 	$_SESSION['error_message'] = "Le nom est requis.";
		// 	header('Location: ' . BASE_URL . '/login');
		// 	exit;
		// }

		// if (strlen($nom) < 2) {
		// 	$_SESSION['error_message'] = "Le nom doit contenir au moins 2 caractères.";
		// 	header('Location: ' . BASE_URL . '/login');
		// 	exit;
		// }

		// // Créer le joueur
		// $joueur = new Joueur($nom, $couleur);

		// // Connecter le joueur
		// AuthMiddleware::login($joueur);

		// $redirect = $_SESSION['redirect_after_login'] ?? '/home';
		// unset($_SESSION['redirect_after_login']);

		// header('Location: ' . BASE_URL . $redirect);
		// exit;

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ' . BASE_URL . '/register');
			exit;
		}

		
		$errors = [];
		$email       = trim($_POST['email'] ?? '');
		$password    = $_POST['mdp'] ?? '';


		$result = AuthService::login($email, $password);
		
		
		if($result['error'])
		{
			$errors['all'] = $result['message'];
		}

		if (!empty($errors)) {
			View::render('auth/login', [
				'title'  => 'Connexion',
				'errors' => $errors,
				'isConnected' => false,
				'old'    => [
					'email'    => $email,
				],
				'styles' => ['pages/auth.css']
			]);

			return;
		}

		$user = $result['user'];
		AuthMiddleware::loginUsers($user);
		HomeController::index();
	}

	/**
	 * Affiche le formulaire d'inscription
	 */
	public function showRegister(): void
	{
		AuthMiddleware::redirectIfAuthenticated();

		View::render('auth/register', [
			'title' => 'Inscription',
			'isConnected' => false,
			'errors' => [],
			'styles' => ['pages/register.css']
		]);
	}

	public function register(): void
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ' . BASE_URL . '/register');
			exit;
		}

		$email       = trim($_POST['email'] ?? '');
		$username    = trim($_POST['username'] ?? '');
		$password    = $_POST['mdp'] ?? '';
		$confirmMdp  = $_POST['confirmMdp'] ?? '';

		$errors = [];

		if (empty($email) || empty($username) || empty($password) || empty($confirmMdp)) {
			$errors['all'] = "Tous les champs sont obligatoires.";
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors['email'] = "L'adresse email n'est pas valide.";
		}

		if (strlen($username) < 3 || strlen($username) > 20) {
			$errors['username'] = "Le nom d'utilisateur doit contenir entre 3 et 20 caractères.";
		}

		if (strlen($password) < 6) {
			$errors['mdp'] = "Le mot de passe doit contenir au moins 6 caractères.";
		}

		if ($password !== $confirmMdp) {
			$errors['confirmMdp'] = "Les mots de passe ne correspondent pas.";
		}

		if (!empty($errors)) {
			View::render('auth/register', [
				'title'  => 'Inscription',
				'errors' => $errors,
				'isConnected' => false,
				'old'    => [
					'email'    => $email,
					'username' => $username
				],
				'styles' => ['pages/register.css']
			]);

			return;
		}

		$result = AuthService::register($email, $username, $password);

		if (!empty($result['error'])) {

			$errors['all'] = $result['message'];

			View::render('auth/register', [
				'title'  => 'Inscription',
				'errors' => $errors,
				'isConnected' => false,
				'old'    => [
					'email'    => $email,
					'username' => $username
				],
				'styles' => ['pages/register.css']
			]);

			return;
		}

		$_SESSION['success_message'] = "Compte créé avec succès !";
		header('Location: ' . BASE_URL . '/login');
		exit;
	}



	/**
	 * Déconnexion
	 */
	public function logout(): void
	{
		AuthMiddleware::logout();
		$_SESSION['success_message'] = "Vous avez été déconnecté avec succès.";
		
		header('Location: ' . BASE_URL . '/home');

		exit;
	}
}