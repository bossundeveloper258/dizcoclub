<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderMail extends Mailable
{
    use Queueable, SerializesModels;


    public $title;
    public $date;
    public $hour;
    public $image;
    public $clients;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($title, $date, $hour, $image, $clients)
    {
        //
        $this->title = $title;
        $this->date = $date;
        $this->hour = $hour;
        $this->image = $image;
        $this->clients = $clients;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Orden de la entrada')->view('emails.orders.confirmation');
    }
}
