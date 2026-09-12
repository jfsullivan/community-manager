<?php

namespace jfsullivan\CommunityManager\Livewire\Filters;

use Livewire\Attributes\Url;

/**
 * Filters a community member listing by lifecycle status. Defaults to
 * `current` so ended (former) and banned members stay out of balance/roster
 * surfaces unless an admin deliberately asks for them.
 */
trait MemberStatusFilter
{
    #[Url]
    public string $memberStatusFilter = 'current';

    public function mountMemberStatusFilter(): void
    {
        if (! isset($this->filters['memberStatusFilter'])) {
            $this->filters['memberStatusFilter'] = $this->memberStatusFilter;
        }
    }

    /** @return array<string, string> value => label */
    public function memberStatusFilterOptions(): array
    {
        return [
            'current' => 'Current',
            'pending' => 'Pending',
            'former' => 'Former',
            'banned' => 'Banned',
        ];
    }

    /**
     * Constrain a users query to memberships of the given community that match
     * the selected lifecycle status. The users query must already join/scope
     * to the community's memberships (see MemberBalancesPage).
     */
    public function applyMemberStatusFilter($query, $community)
    {
        $now = now();
        $morphClass = $community->getMorphClass();

        return $query->whereHas('memberships', function ($query) use ($community, $morphClass, $now) {
            $query->where('memberships.model_id', $community->id)
                ->where('memberships.model_type', $morphClass);

            match ($this->memberStatusFilter) {
                'pending' => $query->whereNull('memberships.start_at'),
                // Former excludes the banned (they get their own bucket) so the
                // segments are mutually exclusive.
                'former' => $query->whereNotNull('memberships.start_at')
                    ->where('memberships.start_at', '<=', $now)
                    ->whereNotNull('memberships.end_at')
                    ->where('memberships.end_at', '<=', $now)
                    ->whereDoesntHave('statuses', function ($statusQuery) {
                        $statusQuery->where('name', 'banned')
                            ->whereRaw('statuses.id = (select max(s2.id) from statuses s2 where s2.model_id = statuses.model_id and s2.model_type = statuses.model_type)');
                    }),
                'banned' => $query->currentStatus('banned'),
                default => $query->whereNotNull('memberships.start_at')
                    ->where('memberships.start_at', '<=', $now)
                    ->where(function ($query) use ($now) {
                        $query->whereNull('memberships.end_at')
                            ->orWhere('memberships.end_at', '>=', $now);
                    }),
            };
        });
    }
}
