<?php
namespace App\Services;
use InvalidArgumentException;
class StripeAmountService
{
 public function minor(float $amount,string $currency):int
 {
  $currency=strtolower($currency);
  if(!is_finite($amount)||$amount<0)throw new InvalidArgumentException('Invalid payment amount.');
  // This store currently saves money with two decimal places. Do not undercharge unsupported three-decimal currencies.
  if(in_array($currency,['bhd','jod','kwd','omr','tnd'],true))throw new InvalidArgumentException('This currency requires a three-decimal checkout implementation.');
  $zero=['bif','clp','djf','gnf','jpy','kmf','krw','mga','pyg','rwf','vnd','vuv','xaf','xof','xpf'];
  if(in_array($currency,array_merge($zero,['isk','ugx']),true)&&abs($amount-round($amount))>0.000001)throw new InvalidArgumentException('This currency does not support fractional charge amounts.');
  return (int)round($amount*(in_array($currency,$zero,true)?1:100));
 }
}
