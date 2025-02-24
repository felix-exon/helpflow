<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketCommentRequest;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Http\Request;
use Inertia\Inertia;


class TicketController extends Controller
{
    public function index(): \Inertia\Response
    {
        $user = auth()->user();
        $tickets = Ticket::with([])
            ->where('department_id', '=', $user->currentTeam->id)
            ->paginate(10);
        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets
        ]);
    }

    public function comment(TicketCommentRequest $request)
    {
        $validated = $request->validated();

        $ticket = Ticket::findOrFail($validated['ticket_id']);

        // Erstelle den Kommentar
        $comment = TicketComment::create([
            'team_id' => $ticket->team_id,
            'ticket_id' => $ticket->id,
            'creator_id' => auth()->id(),
            'content' => $validated['content'],
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        // Aktualisiere das Ticket mit dem letzten Aktivitätszeitpunkt
        $ticket->update([
            'last_activity_at' => now(),
        ]);

        // Wenn der Kommentar extern ist und eine E-Mail gesendet werden soll
        if (!$comment->is_internal && ($validated['send_email'] ?? false)) {
            // Hier könntest du eine E-Mail-Versand-Job dispatchen
            // z.B.: SendTicketCommentEmailJob::dispatch($comment);
        }

        return redirect()->back()->with([
            'success' => 'Kommentar gesendet'
        ]);
    }

    public function show(Ticket $ticket): \Inertia\Response
    {
        $ticket->load(['comments.creator']);
        return Inertia::render('Tickets/Show', [
           'ticket' => $ticket
        ]);
    }
}
