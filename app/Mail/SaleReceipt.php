<?php
namespace App\Mail;
use App\Models\Sale;
use Illuminate\Mail\Mailable;
class SaleReceipt extends Mailable {
 public function __construct(public Sale $sale) {}
 public function build(){return $this->subject('Your receipt #'.$this->sale->id)->view('mail.receipt');}
}
