<?php

namespace App\ValueObjects;

class MSGraphCredentials extends EmailCredentials
{
    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $tenantId,
        public readonly ?string $refreshToken = null,
        public readonly ?string $accessToken = null,
        public readonly ?int $expiresAt = null
    ) {}

    public function validate(): bool
    {
        return !empty($this->clientId)
            && !empty($this->clientSecret)
            && !empty($this->tenantId);
    }

    public function toArray(): array
    {
        return [
            'type' => 'msgraph',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'tenant_id' => $this->tenantId,
            'refresh_token' => $this->refreshToken,
            'access_token' => $this->accessToken,
            'expires_at' => $this->expiresAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            clientId: $data['client_id'],
            clientSecret: $data['client_secret'],
            tenantId: $data['tenant_id'],
            refreshToken: $data['refresh_token'] ?? null,
            accessToken: $data['access_token'] ?? null,
            expiresAt: $data['expires_at'] ?? null
        );
    }
}
