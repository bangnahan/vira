<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'tripay_reference',
        'merchant_ref',
        'payment_method',
        'amount',
        'admin_fee',
        'shipping_cost',
        'total_amount',
        'checkout_url',
        'qr_code_url',
        'pay_code',
        'status',
        'paid_at',
        'expired_at',
        'raw_callback',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
            'raw_callback' => 'array',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'PAID';
    }

    public function isPending(): bool
    {
        return $this->status === 'UNPAID';
    }

    public function isUnpaid(): bool
    {
        return $this->status === 'UNPAID';
    }

    public function getPaymentMethodNameAttribute(): string
    {
        if (! empty($this->raw_callback['payment_name'])) {
            return (string) $this->raw_callback['payment_name'];
        }

        $code = strtoupper((string) $this->payment_method);
        $names = [
            'QRIS2' => 'QRIS (Semua Bank & E-Wallet)',
            'QRIS' => 'QRIS (Semua Bank & E-Wallet)',
            'BCAVA' => 'BCA Virtual Account',
            'BNIVA' => 'BNI Virtual Account',
            'BRIVA' => 'BRI Virtual Account',
            'MANDIRIVA' => 'Mandiri Virtual Account',
            'PERMATAVA' => 'Permata Virtual Account',
            'CIMBVA' => 'CIMB Niaga Virtual Account',
            'BSIVA' => 'BSI Virtual Account',
            'DANAMONVA' => 'Danamon Virtual Account',
            'MUAMALATVA' => 'Muamalat Virtual Account',
            'OCBCVA' => 'OCBC NISP Virtual Account',
            'OTHERBANKVA' => 'Bank Lainnya (Other Bank VA)',
            'OVO' => 'OVO',
            'DANA' => 'DANA',
            'SHOPEEPAY' => 'ShopeePay',
            'ALFAMART' => 'Alfamart',
            'INDOMARET' => 'Indomaret',
            'ALFAMIDI' => 'Alfamidi',
        ];

        return $names[$code] ?? $this->payment_method;
    }

    public function isQris(): bool
    {
        return str_contains(strtoupper((string) $this->payment_method), 'QRIS');
    }

    public function isVirtualAccount(): bool
    {
        return str_ends_with(strtoupper((string) $this->payment_method), 'VA');
    }

    public function isEwallet(): bool
    {
        return in_array(strtoupper((string) $this->payment_method), ['OVO', 'DANA', 'SHOPEEPAY', 'LINKAJA']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getInstructions(): array
    {
        return $this->raw_callback['instructions'] ?? [];
    }
}
