<x-mail::message>
# Confirm your subscription

You asked to receive status updates for **{{ $pageName }}**.

<x-mail::button :url="$confirmUrl">
Confirm subscription
</x-mail::button>

If you did not request this, ignore this message — nothing further will be sent
to this address.
</x-mail::message>
