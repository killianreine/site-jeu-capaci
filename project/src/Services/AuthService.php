<?php

namespace App\Services;
use App\Config\Database;

class AuthService
{
	public static function register($email, $username, $password)
	{
		//Verifie si un email est pris ou pas
		$existingEmail = Database::select('users',['users_email'],['users_email' => $email]);
		if(count($existingEmail) > 0)
		{
			return ['error' => true, 'message' => "Email déjà enregistré"];
		}

		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

		$data = [
			'users_email' => $email,
			'users_username' => $username,
			'users_mdp' => $hashedPassword,
			'users_date_creation' => date("Y-m-d H:i:s")
		];

	
		$id = Database::insert("users", $data);

		if(!isset($id))
			return ['error' => true, 'message' => "Un problème est survenu"];

		return ['error' => false, 'id' => $id];

	}

	public static function login($email, $password)
	{
		$users = Database::select("users", ['users_id','users_username','users_email','users_mdp'],['users_email' => $email]);

		//
		// var_dump($user);
		if(count($users) == 0 )
		{
			return ['error' => true, 'message' => "Email inconnu ou invalide"];
		}

		$user = $users[0];
		if(password_verify($password, $user['users_mdp']))
		{
			return ['error' => false, "user" => $user];
		}
		else
		{
			return ['error' => true, 'message' => "Le mot de passe est incorrect"];	
		}
	}
}