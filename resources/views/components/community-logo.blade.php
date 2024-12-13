<div {{ $attributes->only('class')->merge(['class' => 'inline-block'])}}>
    @if(strlen($slot) > 0)
        {{ $slot }}
    @else
        @if($isSVG && $exists)
            @svg($name, 'inline-block max-w-full h-full')
        @else 
            <img src="{!! $src !!}" />
        @endif
    @endif
</div>