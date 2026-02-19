<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected $guarded = ['id'];

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    public function setModifiedByAttribute($value)
    {
        $this->attributes['modified_by'] = auth()->user()->id ?? null;
    }
}
