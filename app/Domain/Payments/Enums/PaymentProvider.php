<?php

namespace App\Domain\Payments\Enums;

enum PaymentProvider: string
{
    case LocalMock = 'local_mock';
    case MtnMomo = 'mtn_momo';
    case OrangeMoney = 'orange_money';
    case BankCard = 'bank_card';

    public function label(string $locale = 'fr'): string
    {
        return match ($this) {
            self::LocalMock => 'Paiement local de démonstration',
            self::MtnMomo => 'MTN Mobile Money',
            self::OrangeMoney => 'Orange Money',
            self::BankCard => 'Carte Bancaire (Visa/Mastercard)',
        };
    }
}
