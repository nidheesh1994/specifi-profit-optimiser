<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'labor_hours',
        'labor_cost_per_hour',
        'fixed_overheads',
        'target_profit_margin',
        'llm_provider',
        'api_key',
        'model_name',
    ];
}
