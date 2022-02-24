<?php

namespace MediaCloud\Vendor\Illuminate\Support\Testing\Fakes;
use MediaCloud\Vendor\Illuminate\Contracts\Mail\Mailable;
use Illuminate\Mail\PendingMail;

class PendingMailFake extends PendingMail
{
    /**
     * Create a new instance.
     *
     * @param \MediaCloud\Vendor\Illuminate\Support\Testing\Fakes\MailFake  $mailer
     * @return void
     */
    public function __construct($mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Send a new mailable message instance.
     *
     * @param \MediaCloud\Vendor\Illuminate\Contracts\Mail\Mailable  $mailable
     * @return mixed
     */
    public function send(Mailable $mailable)
    {
        return $this->mailer->send($this->fill($mailable));
    }

    /**
     * Send a mailable message immediately.
     *
     * @param \MediaCloud\Vendor\Illuminate\Contracts\Mail\Mailable  $mailable
     * @return mixed
     *
     * @deprecated Use send() instead.
     */
    public function sendNow(Mailable $mailable)
    {
        return $this->send($mailable);
    }

    /**
     * Push the given mailable onto the queue.
     *
     * @param \MediaCloud\Vendor\Illuminate\Contracts\Mail\Mailable  $mailable
     * @return mixed
     */
    public function queue(Mailable $mailable)
    {
        return $this->mailer->queue($this->fill($mailable));
    }
}
