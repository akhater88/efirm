<x-mail::message>
# {{ __('invitations.email_greeting') }}

{{ __('invitations.email_body', ['inviter' => $inviterName, 'workspace' => $workspaceName]) }}

<x-mail::button :url="$acceptUrl">
{{ __('invitations.accept_invitation') }}
</x-mail::button>

{{ __('invitations.email_expires', ['date' => $expiresAt]) }}

{{ __('common.app_name') }}
</x-mail::message>
