<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Fundwallet extends Mailable
{
    use Queueable, SerializesModels;

    public $first_name;
    public $code;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($first_name, $amount)
    {
        $this->first_name = $first_name;
        $this->amount = $amount;
    }

    public function build()
    {
        return $this->view('mail-templates.fundwallet')->with(['name' =>  $this->first_name, 'amount' => $this->amount]);
    }
}
