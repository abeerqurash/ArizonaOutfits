<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class NavigationMenu extends Model
{
    protected $fillable=['name','location','is_active']; protected $casts=['is_active'=>'boolean'];
    public function items(): HasMany{return $this->hasMany(NavigationMenuItem::class)->orderBy('position')->orderBy('id');}
    public function activeItems(): HasMany{return $this->items()->where('is_active',true);}
}
