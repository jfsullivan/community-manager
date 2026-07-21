{{-- Global <x-profile-photo> (successor to jfsullivan/ui-kit's component): photo when a url is given, initials otherwise. --}}
@props(['url' => null, 'name' => null])

@if(!empty($url))
    <img {{ $attributes->merge(['class' => 'rounded-full']) }} alt="" src="{{ asset($url) }}">
@else
    <span {{ $attributes->merge(['class' => 'flex justify-center items-center rounded-full bg-primary-700 text-white']) }}>
        {{ str_initials($name) ?? '' }}
    </span>
@endif
