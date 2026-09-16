<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;

class AiProvider extends Model
{
    protected $table = 'ai_providers';

    protected $fillable = ['name', 'base_url', 'api_key', 'default_model', 'is_active'];

    protected $hidden = ['api_key'];

    protected $casts = [
        'api_key' => 'encrypted',
        'is_active' => 'boolean',
    ];

    /**
     * Only one provider may be active at a time — enforced here so the rule
     * holds regardless of entry point (Filament UI, tinker, seeders, future API).
     */
    protected static function booted(): void
    {
        static::saving(function (AiProvider $provider) {
            if ($provider->is_active) {
                static::query()
                    ->when($provider->exists, fn ($query) => $query->whereKeyNot($provider->getKey()))
                    ->update(['is_active' => false]);
            }
        });
    }

    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
