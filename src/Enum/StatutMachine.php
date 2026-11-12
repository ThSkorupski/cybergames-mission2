<?php

namespace App\Enum;

/**
 * Statut réglé à la main par le personnel.
 * « Occupée » n'est pas stocké : on le déduit d'un voucher en cours sur la machine.
 */
enum StatutMachine: string
{
    case Disponible = 'disponible';
    case Maintenance = 'maintenance';
    case HorsService = 'hors_service';

    public function libelle(): string
    {
        return match ($this) {
            self::Disponible => 'Disponible',
            self::Maintenance => 'En maintenance',
            self::HorsService => 'Hors service',
        };
    }
}
