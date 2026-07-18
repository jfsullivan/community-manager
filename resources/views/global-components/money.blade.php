@props(['amount' => 0])

{{--
    Displays an integer cent amount as currency (e.g. -1234 => -$12.34). Registered as a
    global <x-money> component (replaces the retired ui-kit money component). The optional
    `formatted` attribute is accepted for backwards compatibility and ignored.
--}}
@php
    $cents = $amount instanceof \Brick\Money\Money
        ? $amount->getMinorAmount()->toInt()
        : (int) $amount;
    $display = ($cents < 0 ? '-' : '').'$'.number_format(abs($cents) / 100, 2);
@endphp

<span {{ $attributes->except('formatted') }}>{{ $display }}</span>
