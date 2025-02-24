<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'category',
        'team_id',
        'due_date',
        'creator_id',
        'assigned_to_id',
        'department_id',
        'email_source_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'due_date' => 'datetime',
        'last_activity_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Aktualisiere last_activity_at bei jeder Änderung
        static::saving(function ($ticket) {
            $ticket->last_activity_at = now();
        });

        // Generiere Referenznummer beim Erstellen
        static::creating(function ($ticket) {
            if (empty($ticket->reference_number)) {
                $ticket->reference_number = 'TIC-' . date('Y') . '-' .
                    str_pad((static::whereYear('created_at', date('Y'))->count() + 1), 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function sourceEmail(): BelongsTo
    {
        return $this->belongsTo(Email::class, 'email_source_id');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function close(): void
    {
        $this->closed_at = now();
        $this->status = 'closed';

        // Berechne die Lösungszeit in Minuten
        $this->resolution_time = $this->created_at->diffInMinutes($this->closed_at);

        $this->save();
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['resolved', 'closed']);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['resolved', 'closed']);
    }

    public function scopeForDepartment($query, Department $department)
    {
        return $query->where('department_id', $department->id);
    }

    public function scopeAssignedTo($query, User $user)
    {
        return $query->where('assigned_to_id', $user->id);
    }
}
