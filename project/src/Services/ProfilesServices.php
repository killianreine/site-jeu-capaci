<?php

namespace App\Services;

use App\Config\Database;

class ProfilesServices
{
	public function getUsers($user_id)
	{
		$user = Database::select("users",["*"],["users_id" => $user_id]);

		if(!empty($user))
		{
			return $user[0];
		}
	}

	public function update($users_id, $email, $username,$mdp )
	{
		$data = [
			'users_email' => $email,
			'users_username' => $username,
			'users_mdp' => $mdp 
		];

		$result = Database::update('users',$data,['users_id' => $users_id]);

		return $result;
	}

	
	public function getUserParties($users_id)
	{
		$result = Database::select("archive_partie",["*"],["users_id" => $users_id, "users2_id" => $users_id],"OR");

		return $result;
	}
}