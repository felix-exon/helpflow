<?php

use App\Models\Email;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id();

            // Beziehungen
            $table->foreignIdFor(Team::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Ticket::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'creator_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->foreignIdFor(Email::class)
                ->nullable()
                ->constrained()
                ->onDelete('set null');

            // Inhalt
            $table->text('content');
            $table->boolean('is_internal')->default(false);
            $table->boolean('is_email_imported')->default(false);
            $table->boolean('is_email_reply')->default(false);

            // Email Metadaten
            $table->string('email_message_id')->nullable();
            $table->string('email_subject')->nullable();
            $table->string('email_from_address')->nullable();
            $table->string('email_from_name')->nullable();
            $table->json('email_attachments')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indizes für Performance
            $table->index(['team_id', 'ticket_id', 'created_at']);
            $table->index(['email_id']);
            $table->index(['is_internal']);
            $table->index(['is_email_imported']);
            $table->index(['is_email_reply']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_comments');
    }
};
