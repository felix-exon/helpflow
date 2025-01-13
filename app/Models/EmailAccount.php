<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\ValueObjects\EmailCredentials;
use App\ValueObjects\MSGraphCredentials;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailAccount extends Model
{
    use HasFactory;

    const TYPE_IMAP = 'imap';
    const TYPE_MSGRAPH = 'msgraph';
    const TYPE_GMAIL = 'gmail';

    protected $fillable = [
        'name',
        'type',
        'email',
        'settings',
        'user_id',
        'is_active',
        'last_sync_at',
        'sync_error',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    protected ?EmailCredentials $credentialsObject = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    public function setCredentials(EmailCredentials $credentials): void
    {
        if (!$credentials->validate()) {
            throw new \InvalidArgumentException('Invalid credentials provided');
        }

        $this->credentials = $credentials->toArray();
        $this->credentialsObject = $credentials;
    }

    public function getCredentials(): EmailCredentials
    {
        if ($this->credentialsObject) {
            return $this->credentialsObject;
        }

        if (!$this->credentials) {
            throw new \RuntimeException('No credentials stored for this account');
        }

        $this->credentialsObject = match ($this->type) {
            self::TYPE_MSGRAPH => MSGraphCredentials::fromArray($this->credentials),
                // Weitere Typen hier...
            default => throw new \RuntimeException("Unknown account type: {$this->type}")
        };

        return $this->credentialsObject;
    }

    public function updateAccessToken(string $accessToken, int $expiresAt): void
    {
        $credentials = $this->getCredentials();

        if ($credentials instanceof MSGraphCredentials) {
            $this->setCredentials(new MSGraphCredentials(
                clientId: $credentials->clientId,
                clientSecret: $credentials->clientSecret,
                tenantId: $credentials->tenantId,
                refreshToken: $credentials->refreshToken,
                accessToken: $accessToken,
                expiresAt: $expiresAt
            ));
            $this->save();
        }
    }

    public function updateRefreshToken(string $refreshToken): void
    {
        $credentials = $this->getCredentials();

        if ($credentials instanceof MSGraphCredentials) {
            $this->setCredentials(new MSGraphCredentials(
                clientId: $credentials->clientId,
                clientSecret: $credentials->clientSecret,
                tenantId: $credentials->tenantId,
                refreshToken: $refreshToken,
                accessToken: $credentials->accessToken,
                expiresAt: $credentials->expiresAt
            ));
            $this->save();
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (EmailAccount $account) {
            if ($account->credentialsObject) {
                $account->credentials = $account->credentialsObject->toArray();
            }
        });
    }
}
