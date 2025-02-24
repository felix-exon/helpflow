<?php

namespace App\Traits;

use App\Models\TicketComment;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasComments
{
    /**
     * Get all comments for the ticket.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get only the internal comments.
     */
    public function internalComments(): HasMany
    {
        return $this->comments()->where('is_internal', true);
    }

    /**
     * Get only the external comments.
     */
    public function externalComments(): HasMany
    {
        return $this->comments()->where('is_internal', false);
    }

    /**
     * Get only the email imported comments.
     */
    public function emailComments(): HasMany
    {
        return $this->comments()->where('is_email_imported', true);
    }

    /**
     * Add a comment to this ticket.
     *
     * @param string $content Der Inhalt des Kommentars
     * @param bool $isInternal Ob der Kommentar intern ist
     * @param int|null $creatorId ID des Erstellers oder null
     * @return TicketComment Der erstellte Kommentar
     */
    public function addComment(string $content, bool $isInternal = false, ?int $creatorId = null): TicketComment
    {
        $creatorId = $creatorId ?: auth()->id();

        $comment = $this->comments()->create([
            'team_id' => $this->team_id,
            'creator_id' => $creatorId,
            'content' => $content,
            'is_internal' => $isInternal,
        ]);

        // Aktualisiere letzten Aktivitätszeitpunkt
        $this->update(['last_activity_at' => now()]);

        return $comment;
    }
}
