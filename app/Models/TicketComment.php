<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'ticket_id',
        'creator_id',
        'email_id',
        'content',
        'is_internal',
        'is_email_imported',
        'is_email_reply',
        'email_message_id',
        'email_subject',
        'email_from_address',
        'email_from_name',
        'email_attachments',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'is_email_imported' => 'boolean',
        'is_email_reply' => 'boolean',
        'email_attachments' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    public function isFromAgent(): bool
    {
        return $this->creator_id !== null;
    }

    public function isFromEmail(): bool
    {
        return $this->is_email_imported;
    }

    public function toEmailContent(): array
    {
        return [
            'subject' => $this->ticket->title,
            'content' => $this->content,
            'ticket_reference' => $this->ticket->reference_number,
        ];
    }
}
