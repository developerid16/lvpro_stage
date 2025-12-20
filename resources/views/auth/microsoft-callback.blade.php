<!DOCTYPE html>
<html>
<head>
  <title>Signing you in…</title>
  <script src="{{ asset('/build/js/msal-browser.min.js') }}"></script>
</head>
<body>
<p>Signing you in…</p>
<script>


const msalConfig = {
    auth: {
        clientId: "{{ config('services.azure.client_id') }}",
        authority: "https://login.microsoftonline.com/common",
        redirectUri: "{{ url('/auth/callback') }}"
    }
};


const msalInstance = new msal.PublicClientApplication(msalConfig);

msalInstance.handleRedirectPromise().then(response => {
    if (!response) return;

    fetch('{{ url("/auth/microsoft") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            id_token: response.idToken
        })
    })
    .then(() => window.location.href = '/dashboard');
});
</script>


</body>
</html>
