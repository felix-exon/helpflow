<?php

namespace App\Services\Mail\Providers;

use App\Contracts\Mail\EmailProviderInterface;
use App\Models\EmailAccount;
use App\ValueObjects\Mail\RemoteEmail;
use Carbon\Carbon;

class ImapProvider implements EmailProviderInterface
{
    private ?EmailAccount $account = null;
    private $connection = null;

    public function setAccount(EmailAccount $account): void
    {
        if ($account->type !== 'imap') {
            throw new \InvalidArgumentException('Account must be of type IMAP');
        }
        $this->account = $account;
    }

    public function fetchNewEmails(): array
    {
        $this->connect();

        $settings = $this->account->settings;
        $folder = $settings['folder'] ?? 'INBOX';
        $processedFolder = $settings['archive_folder'] ?? 'Processed';

        $emails = [];
        $mailbox = imap_open(
            $this->getMailboxString($folder),
            $this->account->credentials['username'],
            $this->account->credentials['password']
        );

        if (!$mailbox) {
            throw new \RuntimeException('Could not open mailbox: ' . imap_last_error());
        }

        $messages = imap_search($mailbox, 'UNSEEN');
        if (!$messages) {
            return [];
        }

        foreach ($messages as $messageNum) {
            $header = imap_headerinfo($mailbox, $messageNum);
            $struct = imap_fetchstructure($mailbox, $messageNum);

            // E-Mail Struktur parsen
            $bodyHtml = null;
            $bodyText = null;

            // Für einfache E-Mails
            if (!$struct->parts) {
                $body = imap_body($mailbox, $messageNum);
                if ($struct->subtype === 'HTML') {
                    $bodyHtml = $body;
                } else {
                    $bodyText = $body;
                }
            } else {
                // Für multipart E-Mails
                foreach ($struct->parts as $partNum => $part) {
                    $body = imap_fetchbody($mailbox, $messageNum, $partNum + 1);

                    if ($part->subtype === 'HTML') {
                        $bodyHtml = quoted_printable_decode($body);
                    } elseif ($part->subtype === 'PLAIN') {
                        $bodyText = quoted_printable_decode($body);
                    }
                }
            }

            $emails[] = new RemoteEmail(
                messageId: $header->message_id,
                remoteId: $messageNum,
                subject: $header->subject,
                bodyHtml: $bodyHtml,
                bodyText: $bodyText,
                fromEmail: $header->from[0]->mailbox . '@' . $header->from[0]->host,
                fromName: $header->from[0]->personal ?? null,
                toEmail: $header->to[0]->mailbox . '@' . $header->to[0]->host,
                toName: $header->to[0]->personal ?? null,
                cc: $this->parseAddresses($header->cc ?? []),
                bcc: $this->parseAddresses($header->bcc ?? []),
                headers: $this->parseHeaders(imap_fetchheader($mailbox, $messageNum)),
                receivedAt: Carbon::createFromTimestamp(strtotime($header->date)),
                inReplyToMessageId: $header->in_reply_to ?? null,
                threadId: null // IMAP hat kein natives Thread-ID Konzept
            );
        }

        imap_close($mailbox);
        return $emails;
    }

    public function markEmailAsProcessed(string $remoteId): bool
    {
        $this->connect();

        $settings = $this->account->settings;
        $processedFolder = $settings['archive_folder'] ?? 'Processed';

        try {
            $mailbox = imap_open(
                $this->getMailboxString('INBOX'),
                $this->account->credentials['username'],
                $this->account->credentials['password']
            );

            // Stelle sicher, dass der Ziel-Ordner existiert
            if (!imap_getmailboxes($mailbox, $this->getMailboxString(''), '*')) {
                imap_createmailbox($mailbox, $this->getMailboxString($processedFolder));
            }

            // Verschiebe die E-Mail
            $success = imap_mail_move($mailbox, $remoteId, $processedFolder);
            imap_expunge($mailbox);
            imap_close($mailbox);

            return $success;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function testConnection(): bool
    {
        try {
            $this->connect();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function authenticate(): bool
    {
        // IMAP verwendet Basic Auth, keine zusätzliche Authentifizierung nötig
        return $this->testConnection();
    }

    public function refreshAuth(): bool
    {
        // IMAP verwendet keine Tokens die erneuert werden müssen
        return true;
    }

    private function connect(): void
    {
        if (!$this->account) {
            throw new \RuntimeException('No account set');
        }

        $credentials = $this->account->credentials;
        if (!isset($credentials['host'], $credentials['port'], $credentials['username'], $credentials['password'])) {
            throw new \RuntimeException('Invalid IMAP credentials');
        }
    }

    private function getMailboxString(string $folder): string
    {
        $credentials = $this->account->credentials;
        $encryption = $credentials['encryption'] ?? 'ssl';

        return sprintf(
            '{%s:%d/%s%s}%s',
            $credentials['host'],
            $credentials['port'],
            'imap',
            $encryption ? '/' . $encryption : '',
            $folder
        );
    }

    private function parseAddresses(array $addresses): array
    {
        return array_map(function ($addr) {
            return $addr->mailbox . '@' . $addr->host;
        }, $addresses);
    }

    private function parseHeaders(string $headerString): array
    {
        $headers = [];
        foreach (imap_rfc822_parse_headers($headerString) as $key => $value) {
            if (is_object($value)) {
                continue;
            }
            $headers[$key] = $value;
        }
        return $headers;
    }
}
