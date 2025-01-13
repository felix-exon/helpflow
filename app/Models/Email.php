<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',       // Eindeutige Message-ID der E-Mail
        'subject',
        'body_html',
        'body_text',
        'from_email',
        'from_name',
        'to_email',
        'to_name',
        'cc',              // JSON-Array von E-Mail-Adressen
        'bcc',             // JSON-Array von E-Mail-Adressen
        'ticket_id',
        'email_account_id',
        'remote_id',       // ID/UID in der Ursprungsquelle (IMAP, Graph etc.)
        'headers',         // JSON mit allen Headers
        'received_at',     // Zeitpunkt des E-Mail-Empfangs
        'imported_at',     // Zeitpunkt des Imports ins System
    ];

    protected $casts = [
        'cc' => 'array',
        'bcc' => 'array',
        'headers' => 'array',
        'received_at' => 'datetime',
        'imported_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function emailAccount(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class);
    }

    // Hilfsmethode um zu prüfen ob eine E-Mail bereits importiert wurde
    public static function isAlreadyImported(string $messageId, int $emailAccountId): bool
    {
        return static::where('message_id', $messageId)
            ->where('email_account_id', $emailAccountId)
            ->exists();
    }
}
