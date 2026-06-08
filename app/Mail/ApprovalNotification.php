<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $docNumber,
        public string $approvedBy,
        public string $nextRole,
        public string $nextDept,
        public string $supplier,
        public string $action = 'APPROVED'
    ) {}

    public function envelope(): Envelope
    {
        $subject = match($this->action) {
            'REJECTED'      => "[Rejected] Doc: {$this->docNumber}",
            'FULLY_APPROVED'=> "[Completed] Doc: {$this->docNumber}",
            default         => "[Action Required] Approval Doc: {$this->docNumber}",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.approval-notification',
        );
    }
}