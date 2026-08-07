import Alpine from 'alpinejs';

import './hover-player';
import './video-progress';
import './upload-form';
import './shorts-feed';
import './live-search';

const themePreferences = ['dark', 'light', 'system'];

const resolveTheme = (preference) => preference === 'system'
    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
    : preference;

const applyThemePreference = (preference) => {
    if (! themePreferences.includes(preference)) {
        return;
    }

    const resolved = resolveTheme(preference);

    document.documentElement.dataset.theme = resolved;
    document.documentElement.dataset.themePreference = preference;
    document.documentElement.style.colorScheme = resolved;

    document.querySelectorAll('[data-theme-option]').forEach((option) => {
        option.setAttribute('aria-checked', String(option.dataset.themeOption === preference));
    });
};

const saveThemePreference = (preference) => {
    applyThemePreference(preference);

    try {
        window.localStorage.setItem('turtube-theme-preference', preference);
    } catch {
        // Storage may be unavailable in strict privacy contexts.
    }

    const endpoint = document.body?.dataset.themeEndpoint;

    if (! endpoint) {
        return;
    }

    window.fetch(endpoint, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: JSON.stringify({ theme: preference }),
    }).catch(() => {
        // Local preference remains available if the account sync is unavailable.
    });
};

const initialiseWhenReady = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });
        return;
    }

    callback();
};

const initialiseThemeMenus = () => {
    document.querySelectorAll('[data-theme-menu]').forEach((menu) => {
        const toggle = menu.querySelector('[data-theme-menu-toggle]');
        const panel = menu.querySelector('[data-theme-menu-panel]');

        if (! toggle || ! panel) {
            return;
        }

        const closeMenu = () => {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        };

        toggle.addEventListener('click', () => {
            const opening = panel.hidden;

            document.querySelectorAll('[data-theme-menu-panel]').forEach((otherPanel) => {
                otherPanel.hidden = true;
            });
            document.querySelectorAll('[data-theme-menu-toggle]').forEach((otherToggle) => {
                otherToggle.setAttribute('aria-expanded', 'false');
            });

            panel.hidden = ! opening;
            toggle.setAttribute('aria-expanded', String(opening));
        });

        menu.querySelectorAll('[data-theme-option]').forEach((option) => {
            option.addEventListener('click', () => {
                saveThemePreference(option.dataset.themeOption);
                closeMenu();
            });
        });

        document.addEventListener('click', (event) => {
            if (! menu.contains(event.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeMenu();
                toggle.focus();
            }
        });
    });

    applyThemePreference(document.documentElement.dataset.themePreference || 'dark');

    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addEventListener('change', () => {
        if (document.documentElement.dataset.themePreference === 'system') {
            applyThemePreference('system');
        }
    });
};

initialiseWhenReady(initialiseThemeMenus);

const initialiseSidebarControls = () => {
    const sidebar = document.querySelector('#primary-sidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const overlay = document.querySelector('[data-sidebar-overlay]');

    if (! sidebar || ! toggle || ! overlay) {
        return;
    }

    const desktopBreakpoint = window.matchMedia('(min-width: 1024px)');
    let sidebarOpen = false;
    let sidebarCollapsed = false;

    try {
        sidebarCollapsed = window.localStorage.getItem('turtube-sidebar-collapsed') === 'true';
    } catch {
        // Storage may be unavailable in strict privacy contexts.
    }

    const syncSidebar = () => {
        const isDesktop = desktopBreakpoint.matches;

        sidebar.classList.toggle('is-collapsed', isDesktop && sidebarCollapsed);
        sidebar.classList.toggle('is-open', ! isDesktop && sidebarOpen);
        overlay.classList.toggle('hidden', isDesktop || ! sidebarOpen);
        toggle.setAttribute('aria-expanded', String(isDesktop ? ! sidebarCollapsed : sidebarOpen));
        toggle.setAttribute(
            'aria-label',
            isDesktop
                ? (sidebarCollapsed ? 'Menüyü genişlet' : 'Menüyü daralt')
                : (sidebarOpen ? 'Menüyü kapat' : 'Menüyü aç'),
        );
    };

    const closeSidebar = () => {
        sidebarOpen = false;
        syncSidebar();
    };

    toggle.addEventListener('click', () => {
        if (desktopBreakpoint.matches) {
            sidebarCollapsed = ! sidebarCollapsed;

            try {
                window.localStorage.setItem('turtube-sidebar-collapsed', String(sidebarCollapsed));
            } catch {
                // Storage may be unavailable in strict privacy contexts.
            }
        } else {
            sidebarOpen = ! sidebarOpen;
        }

        syncSidebar();
    });

    document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
        button.addEventListener('click', closeSidebar);
    });

    overlay.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });
    desktopBreakpoint.addEventListener('change', () => {
        sidebarOpen = false;
        syncSidebar();
    });

    syncSidebar();
};

initialiseWhenReady(initialiseSidebarControls);

window.turtubeShell = () => ({
    searchOpen: false,

    init() {
        window.requestAnimationFrame(() => {
            document.documentElement.classList.add('theme-transitions');
        });
    },
});

window.Alpine = Alpine;

Alpine.data('turtubeShell', () => ({
    searchOpen: false,

    init() {
        window.requestAnimationFrame(() => document.documentElement.classList.add('theme-transitions'));
    },
}));

Alpine.start();
