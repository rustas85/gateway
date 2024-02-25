<?php
// app/Mail/VerificationMail.php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationCode; // Добавляем свойство для хранения кода

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($verificationCode) // Добавляем параметр в конструктор
    {
        $this->verificationCode = $verificationCode; // Сохраняем код в свойстве класса
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.verify')
            ->with([
                'verificationCode' => $this->verificationCode, // Используем код в письме
            ]);
    }
}
