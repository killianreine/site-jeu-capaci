<?php

namespace App\Core;


class View
{
	public static function render(string $view, array $data = []): void
	{

		//$data['isConnected'] = isset($_SESSION['joueur']);

		extract($data);

		ob_start();
		require __DIR__ . "/../Views/{$view}.php";
		$content = ob_get_clean();

		require __DIR__ . "/../Views/layout/main.php";
	}
}

