<?php

use Illuminate\Support\Facades\Blade;
use jfsullivan\CommunityManager\Models\Community;

/** The header asks for a logo; apps add the media library, so stub "no logo". */
class PageHeaderCommunity extends Community
{
    protected $table = 'communities';

    public function hasMedia(string $collectionName = 'default', array $filters = []): bool
    {
        return false;
    }
}

it('renders page actions beside the community name when given', function () {
    $community = PageHeaderCommunity::find(Community::factory()->create(['name' => 'Riverside League'])->id);

    $html = Blade::render(<<<'BLADE'
        <x-community-manager::page-header :community="$community">
            <x-slot name="actions"><button>Invite friends</button></x-slot>
        </x-community-manager::page-header>
    BLADE, ['community' => $community]);

    expect($html)->toContain('Riverside League')->toContain('Invite friends');
    expect(strpos($html, 'Invite friends'))->toBeGreaterThan(strpos($html, 'Riverside League'));
});

it('renders no actions area without the slot', function () {
    $community = PageHeaderCommunity::find(Community::factory()->create(['name' => 'Riverside League'])->id);

    $html = Blade::render('<x-community-manager::page-header :community="$community" />', ['community' => $community]);

    expect($html)->toContain('Riverside League')->not->toContain('Invite friends');
});
