<?php
declare(strict_types=1);

namespace App\Models\Enums;

/**
 * Enum des formes (Pierre–Feuille–Ciseaux).
 */
enum Forme: string
{
    case PIERRES = 'PIERRES';
    case FEUILLES = 'FEUILLES';
    case CISEAUX = 'CISEAUX';

    /**
     * Retourne true si $this domine $other (règle Chi Fou Mi).
     */
    public function domine(Forme $autre): bool
    {
        return match ($this) {
            self::CISEAUX  => $autre === self::FEUILLES,
            self::FEUILLES => $autre === self::PIERRES,
            self::PIERRES  => $autre === self::CISEAUX,
        };
    }
}
