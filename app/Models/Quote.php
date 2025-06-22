<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_address',
        'products',
        'labor_hours',
        'labor_cost_per_hour',
        'fixed_overheads',
        'target_profit_margin',
        'calculated_margin',
        'total_profit',
        'total_trade_price',
        'total_retail_price',
        'health_status',
        'ai_model_used',
        'ai_suggestions',
        'chat_log',
        'last_ai_feedback',
        'chat_started_at',
    ];

    protected $casts = [
        'products' => 'array',
        'labor_hours' => 'decimal:2',
        'labor_cost_per_hour' => 'decimal:2',
        'fixed_overheads' => 'decimal:2',
        'target_profit_margin' => 'decimal:2',
        'calculated_margin' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'total_trade_price' => 'decimal:2',
        'total_retail_price' => 'decimal:2',
        'chat_started_at' => 'datetime',
        'last_ai_feedback' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
