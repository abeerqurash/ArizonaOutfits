<?php

namespace App\Mail;

use App\Models\Order;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewOrderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public string $renderedSubject;
    public ?string $renderedBody;
    private string $contentView;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing(['items.product','items.variant','user']);
        $rendered = app(EmailTemplateService::class)->render('admin-new-order', $this->variables(), 'New order received - ' . $this->order->order_number, 'emails.orders.admin-new-order');
        $this->renderedSubject=$rendered['subject']; $this->renderedBody=$rendered['html']; $this->contentView=$rendered['view'];
    }
    public function envelope(): Envelope { return new Envelope(subject:$this->renderedSubject); }
    public function content(): Content { return new Content(view:$this->contentView,with:['renderedSubject'=>$this->renderedSubject,'renderedBody'=>$this->renderedBody]); }
    public function attachments(): array { return []; }
    private function variables(): array
    {
        return ['customer_name'=>$this->order->billing_name ?: $this->order->user?->name ?: 'Guest customer','order_number'=>$this->order->order_number,'order_total'=>number_format((float)$this->order->total,2),'currency'=>$this->order->currency ?: 'USD','payment_status'=>str($this->order->payment_status)->headline(),'admin_order_url'=>route('admin.orders.show',$this->order),'store_name'=>config('app.name')];
    }
}
