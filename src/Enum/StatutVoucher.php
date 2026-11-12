<?php

namespace App\Enum;

/**
 * État d'un voucher, calculé à partir de ses dates (jamais stocké en base).
 */
enum StatutVoucher: string
{
    case NonUtilise = 'non_utilise';
    case EnCours = 'en_cours';
    case Termine = 'termine';
    case Expire = 'expire';

    public function libelle(): string
    {
        return match ($this) {
            self::NonUtilise => 'Non utilisé',
            self::EnCours => 'En cours',
            self::Termine => 'Terminé',
            self::Expire => 'Expiré',
        };
    }
}
