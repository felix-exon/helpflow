<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Department;
use App\Models\Team;
use App\Models\User;
use App\Models\Email;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Team Beziehung
            $table->foreignIdFor(Team::class)->constrained()->cascadeOnDelete();

            // Beziehungen
            $table->foreignIdFor(User::class, 'creator_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignIdFor(User::class, 'assigned_to_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->foreignIdFor(Department::class)
                ->nullable()
                ->constrained()
                ->onDelete('set null');

            $table->foreignIdFor(Email::class, 'email_source_id')
                ->nullable()
                ->constrained('emails')
                ->onDelete('set null');

            // Basis Informationen
            $table->string('reference_number');
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])
                ->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])
                ->default('medium');
            $table->string('category');

            // Termine und Zeiten
            $table->dateTime('due_date')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->integer('resolution_time')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Reference number sollte pro Team eindeutig sein
            $table->unique(['team_id', 'reference_number']);

            // Indices für Performance
            $table->index(['team_id', 'status', 'priority']);
            $table->index(['team_id', 'category']);
            $table->index(['team_id', 'assigned_to_id', 'status']);
            $table->index(['team_id', 'department_id', 'status']);
            $table->index(['team_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
