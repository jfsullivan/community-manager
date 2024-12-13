@props(['community'])

<form method="POST" action="{{ route('current-community.update') }}">
    @method('PUT')
    @csrf

    <!-- Hidden Community ID -->
    <input type="hidden" name="community_id" value="{{ $community->id }}">

    <x-community-manager::dropdown-link onclick="event.preventDefault(); this.closest('form').submit();" :show-arrow="! Auth::user()->isCurrentCommunity($community)">
        
            <x-slot name="icon">
                @if (Auth::user()->isCurrentCommunity($community))
                    <x-apexicon-open.check-circle class="h-5 w-5 text-green-500 stroke-1.5" />

                @else
                    <x-apexicon-open.check-circle class="h-5 w-5 text-transparent stroke-1.5" />
                @endif
            </x-slot>
        
        <div class="truncate">{{ $community->name }}</div>
    </x-community-manager::dropdown-link>
</form>
