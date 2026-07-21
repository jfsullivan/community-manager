<?php

namespace jfsullivan\CommunityManager\Livewire\Pages;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use jfsullivan\CommunityManager\Livewire\Forms\CommunityForm;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateCommunityPage extends Component
{
    public CommunityForm $form;

    #[Computed]
    public function user()
    {
        return Auth::user();
    }

    /**
     * All timezone identifiers as select options (client-side searchable in the form).
     *
     * @return array<int, array{value: string, label: string}>
     */
    #[Computed]
    public function timezoneOptions(): array
    {
        return collect(timezone_identifiers_list())
            ->map(fn ($timezone) => [
                'value' => $timezone,
                'label' => str_replace('_', ' ', $timezone),
            ])
            ->values()
            ->all();
    }

    public function searchTimezones($searchTerm)
    {
        // $timestamp = time();
        // foreach (timezone_identifiers_list(\DateTimeZone::ALL) as $key => $value) {
        //     date_default_timezone_set($value);
        //     $timezone[$value] = $value . ' (UTC ' . date('P', $timestamp) . ')';
        // }
        // $timezones = collect($timezone)->filter(function ($item) use ($searchTerm) {
        //     return stripos($item, $searchTerm) !== false;
        // })->sortKeys();

        $timestamp = time();
        $timezones = collect(timezone_identifiers_list(\DateTimeZone::ALL))->map(function ($timezone) use ($timestamp) {
            date_default_timezone_set($timezone);

            return collect([
                'region' => Str::before($timezone, '/'),
                'value' => $timezone,
                'label' => Str::of($timezone)->after('/')->replace('_', ' ').' (UTC '.date('P', $timestamp).')',
            ])->toArray();

        })->filter(function ($item) use ($searchTerm) {
            return stripos($item['value'], $searchTerm) !== false;
        })->sortBy('label')->groupBy('region')->toArray();

        return $timezones;
    }

    public function save()
    {
        $this->validate();

        $community = $this->form->store();

        if (! $community) {
            $this->addError('form', 'There was a problem creating your community. Please try again.');

            return;
        }

        session()->flash('notify', [
            'type' => 'success',
            'title' => 'Success',
            'message' => 'Your community has been created.',
        ]);

        Auth::user()->switchCommunity($community);

        $this->redirect(route('community.dashboard'));

    }

    public function render()
    {
        return view('community-manager::livewire.pages.create-community-page');
    }
}
