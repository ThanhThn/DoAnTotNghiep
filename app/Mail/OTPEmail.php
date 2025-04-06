<?php

namespace App\Mail;

use App\Models\AdminUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OTPEmail extends Mailable
{
    use Queueable, SerializesModels;

    public  $userName;
    public $subject;
    public $otp;
    /**
     * Create a new message instance.
     */
    public function __construct($userName, $otp, $subject)
    {
        $this->otp = $otp;
        $this->userName = $userName;
        $this->subject = $subject;
    }

    /**
     * Get the message envelope.
     */
    public function build()
    {
        $subject = $this->subject ?? "Yêu cầu đổi mật khẩu - [App Name]";
        return $this->view('opt')->subject($subject)->with([
            'otp' => $this->otp,
            'user_name' => $this->userName,
        ]);
    }
}
