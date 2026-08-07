<script>
    (() => {
        const allowed = ['dark', 'light', 'system'];
        const accountPreference = {{ Illuminate\Support\Js::from(auth()->user()?->theme_preference) }};
        let preference = accountPreference;

        try {
            preference ??= window.localStorage.getItem('turtube-theme-preference');
        } catch {
            // Local storage can be unavailable in strict privacy contexts.
        }

        preference = allowed.includes(preference) ? preference : 'dark';
        const resolved = preference === 'system'
            ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
            : preference;

        document.documentElement.dataset.theme = resolved;
        document.documentElement.dataset.themePreference = preference;
        document.documentElement.style.colorScheme = resolved;
    })();
</script>
