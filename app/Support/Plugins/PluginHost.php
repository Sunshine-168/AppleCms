<?php

namespace App\Support\Plugins;

class PluginHost
{
    /** @var array<string, array<string, mixed>> */
    private array $modules = [];

    /** @var array<string, array<string, mixed>> */
    private array $extraPages = [];

    /** @var array<string, list<array<string, mixed>>> */
    private array $sidebarFold = [];

    /** @var array<string, list<array<string, mixed>>> */
    private array $catalog = [];

    /** @var list<array{url:string,label:string}> */
    private array $settingsLinks = [];

    /** @param array<string, mixed> $config */
    public function module(string $name, array $config): void
    {
        $this->modules[$name] = $config;
    }

    /** @param array<string, mixed> $page */
    public function extraPage(string $key, array $page): void
    {
        $this->extraPages[$key] = $page;
    }

    /** @param array<string, mixed> $item */
    public function sidebarFold(string $group, array $item): void
    {
        $this->sidebarFold[$group][] = $item;
    }

    /** @param array<string, mixed> $item */
    public function catalogItem(string $block, array $item): void
    {
        $this->catalog[$block][] = $item;
    }

    /** @param array{url:string,label:string} $link */
    public function settingsLink(array $link): void
    {
        $this->settingsLinks[] = $link;
    }

    /** @return array<string, mixed>|null */
    public function findModule(string $name): ?array
    {
        return $this->modules[$name] ?? null;
    }

    /** @return array<string, array<string, mixed>> */
    public function modules(): array
    {
        return $this->modules;
    }

    /** @return array<string, array<string, mixed>> */
    public function extraPages(): array
    {
        return $this->extraPages;
    }

    /** @return list<array<string, mixed>> */
    public function sidebarFoldItems(string $group): array
    {
        return $this->sidebarFold[$group] ?? [];
    }

    /** @return list<array<string, mixed>> */
    public function catalogItems(string $block): array
    {
        return $this->catalog[$block] ?? [];
    }

    /** @return list<array{url:string,label:string}> */
    public function settingsLinks(): array
    {
        return $this->settingsLinks;
    }
}
