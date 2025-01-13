<?php

namespace App\ValueObjects\Mail;

class RemoteEmail
{
    public function __construct(
        public readonly string $messageId,
        public readonly string $remoteId,
        public readonly string $subject,
        public readonly ?string $bodyHtml,
        public readonly ?string $bodyText,
        public readonly string $fromEmail,
        public readonly ?string $fromName,
        public readonly string $toEmail,
        public readonly ?string $toName,
        public readonly ?array $cc,
        public readonly ?array $bcc,
        public readonly array $headers,
        public readonly \DateTime $receivedAt,
        public readonly ?string $inReplyToMessageId = null,
        public readonly ?string $threadId = null,
    ) {}

    /**
     * Erstellt eine Instanz aus einem rohen E-Mail-Array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            messageId: $data['message_id'],
            remoteId: $data['remote_id'],
            subject: $data['subject'],
            bodyHtml: $data['body_html'] ?? null,
            bodyText: $data['body_text'] ?? null,
            fromEmail: $data['from_email'],
            fromName: $data['from_name'] ?? null,
            toEmail: $data['to_email'],
            toName: $data['to_name'] ?? null,
            cc: $data['cc'] ?? null,
            bcc: $data['bcc'] ?? null,
            headers: $data['headers'] ?? [],
            receivedAt: new \DateTime($data['received_at']),
            inReplyToMessageId: $data['in_reply_to'] ?? null,
            threadId: $data['thread_id'] ?? null,
        );
    }
}
