<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'user_id',
        'labor_hours',
        'labor_cost_per_hour',
        'fixed_overheads',
        'target_profit_margin',
        'llm_provider',
        'api_key',
        'model_name',
        'connection_status'
    ];

    public static function getForUser()
    {
        return static::where('user_id', Auth::id());
    }
}
