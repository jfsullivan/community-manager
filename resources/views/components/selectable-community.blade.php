@props(['community'])

<form method="POST" action="{{ route('current-community.update') }}">
    @method('PUT')
    @csrf

    <!-- Hidden Community ID -->
    <input type="hidden" name="community_id" value="{{ $community->id }}">

    <a href="#" onclick="event.preventDefault(); this.closest('form').submit();">
        {{ $slot }}
    </a>
</form>
