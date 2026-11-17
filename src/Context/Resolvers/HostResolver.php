<?php

namespace jfsullivan\CommunityManager\Context\Resolvers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use jfsullivan\CommunityManager\Contracts\CommunityResolver;

/**
 * The community whose site the request is on (config('community-manager.hosts')):
 *
 * 1. a central domain resolves to none;
 * 2. {slug}.{base_domain}: exactly one lowercase label, not reserved, matched
 *    against the slug column;
 * 3. otherwise the host is looked up in the cached custom-domain map.
 *
 * Hosts are compared lowercase, without a port or trailing dot. Resolve from a
 * host the app trusts (TrustHosts): the request's Host header is the input.
 */
class HostResolver implements CommunityResolver
{
    /** A single DNS label: letters, digits and inner hyphens, at most 63 characters. */
    protected const LABEL = '/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/';

    public function resolve(Request $request): ?Model
    {
        return $this->forHost($request->getHost());
    }

    public function forHost(?string $host): ?Model
    {
        $host = static::normalize($host);

        if ($host === null || $this->isCentral($host)) {
            return null;
        }

        $baseDomain = static::normalize(config('community-manager.hosts.base_domain'));

        if ($baseDomain !== null && str_ends_with($host, '.'.$baseDomain)) {
            return $this->forSubdomain(substr($host, 0, -strlen('.'.$baseDomain)));
        }

        return $this->forCustomDomain($host);
    }

    /** The community at {label}.{base_domain}, if the label is a valid, unreserved slug. */
    public function forSubdomain(string $label): ?Model
    {
        $slugColumn = config('community-manager.hosts.slug_column', 'slug');

        if (! $slugColumn || ! preg_match(self::LABEL, $label) || $this->isReserved($label)) {
            return null;
        }

        return $this->query()->where($slugColumn, $label)->first();
    }

    public function forCustomDomain(string $host): ?Model
    {
        if (! config('community-manager.hosts.custom_domain_column', 'custom_domain')) {
            return null;
        }

        $communityId = static::customDomains()[$host] ?? null;

        return $communityId === null ? null : $this->query()->find($communityId);
    }

    public function isCentral(string $host): bool
    {
        $central = collect((array) config('community-manager.hosts.central_domains', []))
            ->map(fn ($domain) => static::normalize($domain))
            ->filter();

        return $central->contains($host);
    }

    public function isReserved(string $label): bool
    {
        return in_array($label, array_map('strtolower', (array) config('community-manager.hosts.reserved_subdomains', [])), true);
    }

    /**
     * Lowercase custom domain => community key, cached.
     *
     * @return array<string, int|string>
     */
    public static function customDomains(): array
    {
        $column = config('community-manager.hosts.custom_domain_column', 'custom_domain');

        if (! $column) {
            return [];
        }

        return Cache::remember(
            config('community-manager.hosts.cache_key', 'community-manager.custom-domains'),
            (int) config('community-manager.hosts.cache_ttl', 3600),
            function () use ($column) {
                $communityClass = config('community-manager.community_model');

                return $communityClass::query()
                    ->whereNotNull($column)
                    ->pluck((new $communityClass)->getKeyName(), $column)
                    ->mapWithKeys(fn ($id, $domain) => [static::normalize($domain) => $id])
                    ->filter(fn ($id, $domain) => $domain !== '')
                    ->all();
            },
        );
    }

    /** Drop the cached custom-domain map (done automatically when a community's custom domain changes). */
    public static function forgetCustomDomains(): void
    {
        Cache::forget(config('community-manager.hosts.cache_key', 'community-manager.custom-domains'));
    }

    public static function normalize(?string $host): ?string
    {
        if ($host === null) {
            return null;
        }

        $host = strtolower(trim($host));
        $host = preg_replace('/:\d+$/', '', $host);
        $host = rtrim((string) $host, '.');

        return $host === '' ? null : $host;
    }

    protected function query()
    {
        $communityClass = config('community-manager.community_model');

        return $communityClass::query();
    }
}
