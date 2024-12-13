<x-email.layout site-name="{{ config('app.name') }}" :no-reply="$no_reply">

    <x-email.title-heading>You've been invited!</x-email.title-heading>

    @{{{ body }}}

    <x-email.divider />

    <x-email.paragraph>A {{ config('app.name') }} {{ __('Community') }} has been setup to manage @{{{ community_name }}}.  Click the link below to join the fun!</x-email.paragraph>

    @if(!$existing_user)
        <x-email.section-heading>Need a {{ config('app.name') }} account?</x-email.section-heading>
        <x-email.paragraph>Create a {{ config('app.name') }} account by clicking the button below. Use the same email address that this message was sent to in order to accept the invitation.  After creating an account, you may accept the invitation from your {{ config('app.name') }} home page.</x-email.paragraph>

        <x-email.button url="{{ $registerUrl }}">
            Create Account
        </x-email.button>
    @else

        {{-- <x-email.section-heading>Already have a {{ config('app.name') }} account?</x-email.section-heading> --}}
        {{-- <x-email.paragraph>Accept this invitation by clicking the button below.</x-email.paragraph> --}}

        <x-email.button url="{{ $acceptUrl }}">
            Accept Invitation
        </x-email.button>

    @endif

    <x-email.paragraph>If you did not expect to receive an invitation from {{ __('community') }}, you may discard this email.</x-email.paragraph>

</x-email.layout>
