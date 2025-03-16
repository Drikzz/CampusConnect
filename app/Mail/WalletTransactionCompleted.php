<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\WalletTransaction;

class WalletTransactionCompleted extends Mailable
{
  use Queueable, SerializesModels;

  /**
   * The transaction instance.
   *
   * @var \App\Models\WalletTransaction
   */
  public $transaction;

  /**
   * Create a new message instance.
   *
   * @param  \App\Models\WalletTransaction  $transaction
   * @return void
   */
  public function __construct(WalletTransaction $transaction)
  {
    $this->transaction = $transaction;
  }

  /**
   * Get the message envelope.
   *
   * @return \Illuminate\Mail\Mailables\Envelope
   */
  public function envelope()
  {
    return new Envelope(
      subject: 'Withdrawal Successfully Completed - Campus Connect',
    );
  }

  /**
   * Get the message content definition.
   *
   * @return \Illuminate\Mail\Mailables\Content
   */
  public function content()
  {
    return new Content(
      view: 'emails.wallet-withdrawal-completed',
    );
  }

  /**
   * Get the attachments for the message.
   *
   * @return array
   */
  public function attachments()
  {
    return [];
  }
}
