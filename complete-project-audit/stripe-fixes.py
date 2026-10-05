from pathlib import Path
import json
r=Path.cwd();o=r/'complete-project-audit/replacement-files';m=json.loads((o/'manifest.json').read_text())
def save(p,s):
 old=next((x for x in m if x['destination']==p),None);n=old['file'] if old else f'{len(m)+1:02d}_'+Path(p).name+'.txt'
 if not old:m.append({'file':n,'destination':p})
 (o/n).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8')
save('app/Services/StripeAmountService.php',(r/'complete-project-audit/StripeAmountService.php').read_text(encoding='utf-8-sig'))
for p in ['app/Http/Controllers/Payment/StripePaymentController.php','app/Http/Controllers/Webhooks/StripeWebhookController.php']:
 s=(r/p).read_text(encoding='utf-8-sig');start=s.index('    private function convertToMinorUnit(');body=s.index('{',start);depth=1;end=body+1
 while depth:
  if s[end]=='{':depth+=1
  if s[end]=='}':depth-=1
  end+=1
 s=s[:body+1]+"\n        return app(\\App\\Services\\StripeAmountService::class)->minor($amount,$currency);\n    "+s[end-1:]
 if '/Webhooks/' in p:
  start=s.index('    private function verifyPaymentMatchesOrder(');pos=s.index('        $metadataOrderId =',start)
  s=s[:pos]+"""        if ($paymentIntent->status === 'succeeded' && (int) $paymentIntent->amount_received !== $expectedAmount) {
            throw new \\RuntimeException('Stripe received amount does not match the complete order total.');
        }
        if ($order->payment_intent_id && $order->payment_intent_id !== $paymentIntent->id) {
            throw new \\RuntimeException('Stripe PaymentIntent does not match the current order intent.');
        }

"""+s[pos:]
 save(p,s)
(o/'manifest.json').write_text(json.dumps(m,indent=2),encoding='utf-8')
