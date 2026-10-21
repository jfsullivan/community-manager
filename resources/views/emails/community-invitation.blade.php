{{-- Replies go to the inviter (Reply-To), so no "do not reply" footer. --}}
<x-email.layout site-name="{{ config('app.name') }}" :no_reply="false">

    <x-email.title-heading>You've been invited!</x-email.title-heading>

    @{{{ body }}}

    {{-- The inviter's personal note: minimal rich text, sanitized to a strict allowlist. --}}
    @if(!empty($personalMessage))
        <x-email.paragraph>{{ ! empty($inviterName) ? $inviterName.' wrote:' : 'A note from the sender:' }}</x-email.paragraph>
        <div style="margin: 0 0 16px; padding: 12px 16px; border-left: 3px solid #d4d4d8; color: #3f3f46; font-size: 14px; line-height: 1.5;">{!! $personalMessage !!}</div>
    @endif

    <x-email.divider />

    <x-email.paragraph>A {{ config('app.name') }} {{ __('Community') }} has been setup to manage @{{{ community_name }}}.  Click the link below to join the fun!</x-email.paragraph>

    @if(!empty($communityDescription))
        <x-email.paragraph>{!! nl2br(e($communityDescription)) !!}</x-email.paragraph>
    @endif

    @if(!$existing_user)
        <x-email.section-heading>Need a {{ config('app.name') }} account?</x-email.section-heading>
        <x-email.paragraph>Create a {{ config('app.name') }} account by clicking the button below. Use the same email address that this message was sent to in order to accept the invitation.  After creating an account, you may accept the invitation from your {{ config('app.name') }} home page.</x-email.paragraph>

        <x-email.button :url="$registerUrl">
            Create Account
        </x-email.button>

        <x-email.action-url :url="$registerUrl" />
    @else

        {{-- <x-email.section-heading>Already have a {{ config('app.name') }} account?</x-email.section-heading> --}}
        {{-- <x-email.paragraph>Accept this invitation by clicking the button below.</x-email.paragraph> --}}

        <x-email.button :url="$acceptUrl">
            Accept Invitation
        </x-email.button>

        <x-email.action-url :url="$acceptUrl" />

    @endif

    <x-email.paragraph>If you did not expect to receive an invitation from {{ __('community') }}, you may discard this email.</x-email.paragraph>

</x-email.layout>
