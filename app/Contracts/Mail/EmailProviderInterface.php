<?php

namespace App\Contracts\Mail;

use App\Models\EmailAccount;

interface EmailProviderInterface
{
    /**
     * Initialisiert den Provider mit einem Email-Account
     */
    public function setAccount(EmailAccount $account): void;

    /**
     * Holt neue E-Mails vom Provider
     *
     * @return array<RemoteEmail> Array von RemoteEmail Value Objects
     */
    public function fetchNewEmails(): array;

    /**
     * Markiert eine E-Mail als verarbeitet
     */
    public function markEmailAsProcessed(string $remoteId): bool;

    /**
     * Prüft die Verbindung zum Provider
     */
    public function testConnection(): bool;

    /**
     * Authentifiziert den Provider (z.B. OAuth2 Flow)
     */
    public function authenticate(): bool;

    /**
     * Aktualisiert die Auth-Tokens wenn nötig
     */
    public function refreshAuth(): bool;
}
