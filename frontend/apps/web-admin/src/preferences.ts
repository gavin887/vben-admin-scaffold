import { defineOverridesPreferences } from '@vben/preferences';

export const overridesPreferences = defineOverridesPreferences({
  app: {
    name: import.meta.env.VITE_APP_TITLE,
    accessMode: 'backend',
    defaultHomePath: '/workspace',
    enableRefreshToken: false,
    defaultAvatar: '/avatar.svg',
  },
  tabbar: { persist: false },
  copyright: { enable: false },
  logo: { enable: true, source: import.meta.env.VITE_LOGO },
});
