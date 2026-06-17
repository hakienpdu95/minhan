<?php

namespace App\Foundation\Vertical;

use App\Foundation\VerticalDefinition;

class DatabaseVertical implements VerticalDefinition
{
    public function __construct(private readonly VerticalTemplate $template) {}

    public function code(): string               { return $this->template->code; }
    public function label(): string              { return $this->template->label; }
    public function targetLabel(): string        { return $this->template->target_label; }
    public function targetOrgCategory(): string  { return $this->template->target_org_category; }

    public function defaultSiteLabel(): ?string
    {
        return $this->template->default_hierarchy['site'] ?? null;
    }

    public function areaLabel(): ?string
    {
        return $this->template->has_physical_assets
            ? ($this->template->default_hierarchy['area'] ?? null)
            : null;
    }

    public function lotLabel(): ?string
    {
        return $this->template->has_physical_assets
            ? ($this->template->default_hierarchy['lot'] ?? null)
            : null;
    }

    public function itemLabel(): ?string
    {
        return $this->template->has_physical_assets
            ? ($this->template->default_hierarchy['item'] ?? null)
            : null;
    }

    public function defaultItemCodePrefix(): ?string
    {
        return $this->template->default_hierarchy['item_prefix'] ?? null;
    }

    public function phases(): array            { return $this->template->phases ?? []; }
    public function defaultChecklist(): array  { return $this->template->default_checklist ?? []; }
    public function readinessTemplateSlag(): ?string { return $this->template->readiness_template_slug; }
    public function exportConfig(): ?array     { return $this->template->export_config; }
    public function defaultActivityTypes(): ?array  { return $this->template->default_activity_types; }
    public function defaultLegalDocTypes(): ?array  { return $this->template->default_legal_doc_types; }
    public function verticalRoles(): array     { return $this->template->default_roles ?? []; }
    public function sidebarGroups(): array     { return $this->template->sidebar_config ?? []; }

    public function template(): VerticalTemplate { return $this->template; }
}
