<?php

namespace App\Mail;

use App\Models\WorkspaceInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkspaceInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly WorkspaceInvitation $invitation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('invitations.email_subject', [
                'workspace' => $this->invitation->workspace->name,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.workspace-invitation',
            with: [
                'invitation' => $this->invitation,
                'acceptUrl' => url("/invitations/{$this->invitation->token}"),
                'workspaceName' => $this->invitation->workspace->name,
                'inviterName' => $this->invitation->invitedBy->name,
                'expiresAt' => $this->invitation->expires_at->toDateTimeString(),
            ],
        );
    }
}
