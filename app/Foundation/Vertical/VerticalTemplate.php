<?php

namespace App\Foundation\Vertical;

use App\Foundation\VerticalRegistry;
use Illuminate\Database\Eloquent\Model;

class VerticalTemplate extends Model
{
    protected $fillable = [
        'code', 'label', 'target_label', 'target_org_category',
        'has_physical_assets', 'export_config',
        'readiness_template_slug', 'data_collection_template_slug',
        'phases', 'default_checklist', 'default_activity_types', 'default_legal_doc_types',
        'default_hierarchy', 'default_roles', 'sidebar_config', 'is_active',
    ];

    protected $casts = [
        'phases'                  => 'array',
        'default_checklist'       => 'array',
        'default_activity_types'  => 'array',
        'default_legal_doc_types' => 'array',
        'default_hierarchy'       => 'array',
        'default_roles'           => 'array',
        'sidebar_config'          => 'array',
        'export_config'           => 'array',
        'has_physical_assets'     => 'boolean',
        'is_active'               => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn($t)   => VerticalRegistry::clearCache($t->code));
        static::deleted(fn($t) => VerticalRegistry::clearCache($t->code));
    }
}
