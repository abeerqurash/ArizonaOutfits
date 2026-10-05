<?php

namespace App\Mail;

use App\Models\InventoryAlert;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InventoryAlertMail extends Mailable
{
    use Queueable, SerializesModels;
    public string $renderedSubject;
    public ?string $renderedBody;
    private string $contentView;

    public function __construct(public readonly InventoryAlert $alert)
    {
        $this->alert->loadMissing(['product','variant.product']);
        $fallback=($this->alert->isOutOfStock()?'Out of stock: ':'Low stock: ').$this->alert->item_name;
        $rendered=app(EmailTemplateService::class)->render('inventory-alert',$this->variables(),$fallback,'emails.inventory-alert');
        $this->renderedSubject=$rendered['subject']; $this->renderedBody=$rendered['html']; $this->contentView=$rendered['view'];
    }
    public function envelope(): Envelope { return new Envelope(subject:$this->renderedSubject); }
    public function content(): Content { return new Content(view:$this->contentView,with:['renderedSubject'=>$this->renderedSubject,'renderedBody'=>$this->renderedBody]); }
    public function attachments(): array { return []; }
    private function variables(): array
    {
        return ['alert_type'=>$this->alert->isOutOfStock()?'Out of stock':'Low stock','item_name'=>$this->alert->item_name,'current_stock'=>$this->alert->stock_level,'threshold'=>$this->alert->threshold,'inventory_url'=>route('admin.inventory-alerts.index'),'store_name'=>config('app.name')];
    }
}
