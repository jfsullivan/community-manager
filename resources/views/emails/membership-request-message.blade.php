<x-email.layout site-name="{{ config('app.name') }}" :no_reply="false">

    <x-email.title-heading>A message about joining {{ $communityName }}</x-email.title-heading>

    <x-email.paragraph>
        <strong>{{ $senderName }}</strong> ({{ $senderEmail }})
        @if ($requestState === 'pending')
            is waiting for their request to join <strong>{{ $communityName }}</strong> to be confirmed, and sent you this message.
        @elseif ($requestState === 'rejected')
            had their request to join <strong>{{ $communityName }}</strong> turned down, and sent you this message.
        @else
            is no longer a member of <strong>{{ $communityName }}</strong>, and sent you this message.
        @endif
        Reply to this email to answer them directly.
    </x-email.paragraph>

    <div style="margin: 0 0 16px; padding: 12px 16px; border-left: 3px solid #d4d4d8; color: #3f3f46; font-size: 14px; line-height: 1.5;">{!! nl2br(e($body)) !!}</div>

    <x-email.paragraph>
        You can confirm or review requests on your community's Members page.
    </x-email.paragraph>

</x-email.layout>
