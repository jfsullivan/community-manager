<?php

namespace jfsullivan\CommunityManager\Livewire\Pages;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use jfsullivan\CommunityManager\Livewire\Filters\TransactionTypeFilter;
use jfsullivan\CommunityManager\Livewire\Forms\CommunityForm;
use jfsullivan\CommunityManager\Models\TransactionType;
use jfsullivan\UiKit\Livewire\Datatable\Filters\SearchFilter;
use jfsullivan\UiKit\Livewire\Datatable\WithFilters;
use jfsullivan\UiKit\Livewire\Datatable\WithPerPagePagination;
use jfsullivan\UiKit\Livewire\Datatable\WithSorting;
use jfsullivan\UiKit\Livewire\Datatable\WithSelectables;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Attributes\Url;
use Spatie\LaravelOptions\Options;
use Jackiedo\Timezonelist\Facades\Timezonelist;
use Illuminate\Support\Str;

class CreateCommunityPage extends Component
{
    public CommunityForm $form;

    #[Computed]
    public function user()
    {
        return Auth::user();
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
        $timezones = collect(timezone_identifiers_list(\DateTimeZone::ALL))->map(function($timezone) use ($timestamp) {
            date_default_timezone_set($timezone);
            
            return collect([
                'region' => Str::before($timezone, '/'),
                'value' => $timezone,
                'label' => Str::of($timezone)->after('/')->replace('_', ' ') . ' (UTC ' . date('P', $timestamp) . ')',
            ])->toArray();

        })->filter(function ($item) use ($searchTerm) {
            return stripos($item['value'], $searchTerm) !== false;
        })->sortBy('label')->groupBy('region')->toArray();
ray($timezones);
return $timezones;
// ray(Options::forModels($timezones, value: 'value')->toArray());
    // ->append(fn($timezone) => [
    //     'region' => $timezone['region'],
    // ])
    // ->toArray())->groupBy('region');
        // return collect(Options::forModels($timezones, value: 'value')->append(fn($timezone) => [
        //     'region' => $timezone['region'],
        // ])->toArray())->groupBy('region');

        // ray($timezones);

        // return $timezones;
        // $timezones = collect($timezone)->filter(function ($item) use ($searchTerm) {
        //     return stripos($item, $searchTerm) !== false;
        // })->sortKeys();

// $timezoneList = Timezonelist::toArray(false);
// $timezoneList = collect($timezoneList)->map(function($timezoneGroupOptions) {
//     return Options::forArray($timezoneGroupOptions)->toArray();
// })->toArray();
// ray($timezoneList);
// return $timezoneList;
        return Options::forModels($timezones)->toArray();
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
