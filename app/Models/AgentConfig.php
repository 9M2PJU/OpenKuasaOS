<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentConfig extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'agent_configs';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'org_id',
        'agent_name',
        'provider',
        'gemini_api_key',
        'gemini_model',
        'ollama_url',
        'ollama_model',
        'model',
        'system_prompt',
        'tools_enabled',
        'temperature',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'gemini_api_key' => 'encrypted',
            'ollama_url' => 'encrypted',
            'tools_enabled' => 'array',
            'temperature' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AgentRun::class, 'agent_config_id');
    }
}
