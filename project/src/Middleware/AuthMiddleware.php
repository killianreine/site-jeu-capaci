<?php

namespace App\Middleware;

/**
 * Middleware d'authentification
 * Vérifie si l'utilisateur est connecté
 */
class AuthMiddleware
{
    //TODO: Pareil que pour login, la ca serait plutot dansPartie 
    /**
     * Vérifie si l'utilisateur est connecté
     */
    public static function isConnect(): bool
    {
        return isset($_SESSION['users_id']) && !empty($_SESSION['users_id']);
        //return isset($_SESSION['joueur']) && !empty($_SESSION['joueur']);
    }

    /**
     * Vérifie si l'utilisateur est connecté, sinon redirige vers login
     */
    public static function requireAuth(): void
    {
        if (!self::isConnect()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $_SESSION['error_message'] = "Vous devez être connecté pour accéder à cette page.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /**
     * Redirige vers l'accueil si déjà connecté
     */
    public static function redirectIfAuthenticated(): void
    {
        if (self::isConnect()) {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }
    }

    /**
     * Récupère le joueur connecté
     */
    public static function getConnectdUser()
    {
        if (!self::isConnect()) {
            return null;
        }
        
        return $_SESSION['users_id'];
    }

    public static function loginUsers($user)
    {
        $_SESSION['users_id'] = $user['users_id'];
        $_SESSION['users_name'] = $user['users_username'];
    }

    //TODO:Mettre joinGame au lieu de login ca se confond avec la connexion au site
    /**
     * Connecte un joueur
     */
    public static function login($joueur): void
    {
        $_SESSION['joueur'] = $joueur;
        $_SESSION['joueur_nom'] = $joueur->getNom();
        $_SESSION['joueur_couleur'] = $joueur->getCouleur();
    }

    /**
     * Déconnecte le joueur
     */
    public static function logout(): void
    {
        unset($_SESSION['joueur']);
        unset($_SESSION['joueur_nom']);
        unset($_SESSION['joueur_couleur']);
        unset($_SESSION['jeu']);
        session_destroy();
    }
}