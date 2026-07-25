<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\CustomerFactory;

final class Customer extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected $table = 'crm_customers';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'organisation_id',
        'type',
        'status',
        'name',
        'company_name',
        'email',
        'phone',
        'mobile',
        'website',
        'address_line_1',
        'address_line_2',
        'city',
        'county',
        'postal_code',
        'country',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
