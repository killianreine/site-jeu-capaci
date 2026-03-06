<?php

namespace App\Services\Partie;

use App\Config\Database;

/**
 * Service de gestion des codes de partie
 * Responsable de la génération et gestion des codes d'attente
 */
class CodeManagementService
{
    /**
     * Génère un code unique à 6 chiffres
     */
    public function generateCode(): int
    {
        $maxAttempts = 100;
        $attempts = 0;

        do {
            $code = random_int(100000, 999999);
            $exists = $this->codeExists($code);
            $attempts++;
        } while ($exists && $attempts < $maxAttempts);

        if ($exists) {
            throw new \Exception("Impossible de générer un code unique après $maxAttempts tentatives");
        }

        return $code;
    }

    /**
     * Vérifie si un code existe déjà (dans codes_attente, Partie ou rematch_invitations)
     */
    private function codeExists(int $code): bool
    {
        $existsWaiting = Database::select("codes_attente", ['code'], ['code' => $code]);
        $existsPartie = Database::select("partie", ['partie_code'], ['partie_code' => $code]);
        $existsInvite = Database::select("rematch_invitations", ['code'], ['code' => $code]);
        
        return !empty($existsWaiting) || !empty($existsPartie) || !empty($existsInvite);
    }

    /**
     * Récupère les données d'un code d'attente
     */
    public function findByCode(int $code): ?array
    {
        $rows = Database::select("codes_attente", ['*'], ['code' => $code]);
        return empty($rows) ? null : $rows[0];
    }

    /**
     * Insère un code dans la table d'attente
     */
    public function insertWaitingCode(int $code, int $userId, string $username, bool $isPublic = false): void
    {
        $existing = Database::select("codes_attente", ['code'], ['code' => $code]);
        if (!empty($existing)) {
            return;
        }
        Database::insert("codes_attente", [
            'code'       => $code,
            'user_id'    => $userId,
            'username'   => $username,
            'is_public'  => $isPublic ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Supprime un code de la table d'attente
     */
    public function removeWaitingCode(int $code): void
    {
        Database::delete("codes_attente", ['code' => $code]);
    }

    public function updateVisibility(int $code, bool $isPublic): void
    {
        // Utilisation de 'is_public' pour correspondre à ta table codes_attente
        Database::update("codes_attente", 
            ['is_public' => $isPublic ? 1 : 0], 
            ['code' => $code]
        );
    }

    public function getVisibility(int $code): bool 
    {
        $res = $this->findByCode($code);
        // On vérifie 'is_public' ici
        return (bool)($res['is_public'] ?? true);
    }
}