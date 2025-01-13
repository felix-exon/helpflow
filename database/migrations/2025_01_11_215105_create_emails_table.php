<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Ticket;
use App\Models\EmailAccount;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(EmailAccount::class)->constrained()->onDelete('cascade');
            $table->string('message_id')->index();
            $table->string('remote_id')->index();
            $table->string('subject');
            $table->text('body_html')->nullable();
            $table->text('body_text')->nullable();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->json('headers')->nullable();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('imported_at')->useCurrent();
            $table->timestamps();

            // Eine E-Mail darf pro Account nur einmal importiert werden
            $table->unique(['message_id', 'email_account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
