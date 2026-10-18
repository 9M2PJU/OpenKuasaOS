<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgSmtpSetting extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'org_smtp_settings';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'is_active',
    ];

    /**
     * Sembunyikan maklumat sensitif daripada array / JSON serialization.
     */
    protected $hidden = [
        'mail_password',
    ];

    /**
     * Cast attributes:
     * mail_password disulitkan automatik (AES-256-CBC) dalam pangkalan data.
     */
    protected $casts = [
        'mail_password' => 'encrypted',
        'mail_port' => 'integer',
        'is_active' => 'boolean',
    ];

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class, 'org_id');
    }

    /**
     * Semak sama ada kata laluan telah ditetapkan dalam rekod.
     */
    public function hasPassword(): bool
    {
        return !empty($this->mail_password);
    }
}
