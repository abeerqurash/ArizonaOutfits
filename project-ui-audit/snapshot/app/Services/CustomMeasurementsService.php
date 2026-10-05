<?php
namespace App\Services;
use Illuminate\Support\Facades\Validator;
class CustomMeasurementsService
{
 public const FIELDS = ['chest'=>'Chest','waist'=>'Waist','shoulder'=>'Shoulder','sleeve_length'=>'Sleeve length','body_length'=>'Jacket/body length'];
 public function required(array $options): bool {
  foreach($options as $option) {
   if(preg_match('/\bsize\b/i',(string)($option['option_name']??'')) && preg_match('/\bcustom\b/i',(string)($option['value_label']??''))) return true;
  }
  return false;
 }
 public function validate(array $options, mixed $measurements): array {
  if(!$this->required($options)) return [];
  $rules=['custom_measurements'=>'required|array'];
  foreach(self::FIELDS as $key=>$label) $rules['custom_measurements.'.$key]='required|numeric|min:0.1|max:200';
  $validated=Validator::make(['custom_measurements'=>$measurements],$rules)->validate();
  $result=[];
  foreach(self::FIELDS as $key=>$label) $result[$key]=number_format((float)$validated['custom_measurements'][$key],2,'.','');
  return $result;
 }
 public function orderOptions(array $options, mixed $measurements): array {
  foreach($this->validate($options,$measurements) as $key=>$value) $options[]=['option_name'=>self::FIELDS[$key].' (inches)','value_label'=>$value];
  return $options;
 }
}
