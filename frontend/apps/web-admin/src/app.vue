<script lang="ts" setup>
import { computed } from 'vue';

import { useAntdDesignTokens } from '@vben/hooks';
import { preferences, usePreferences } from '@vben/preferences';

import { App, ConfigProvider, theme } from 'ant-design-vue';

import { antdLocale } from '#/locales';

defineOptions({ name: 'App' });

const { isDark } = usePreferences();
const { tokens } = useAntdDesignTokens();

const tokenTheme = computed(() => {
  const algorithm = isDark.value
    ? [theme.darkAlgorithm]
    : [theme.defaultAlgorithm];

  // antd 紧凑模式算法
  if (preferences.app.compact) {
    algorithm.push(theme.compactAlgorithm);
  }

  return {
    algorithm,
    token: tokens,
  };
});
</script>

<template>
  <ConfigProvider :locale="antdLocale" :theme="tokenTheme">
    <App>
      <RouterView />
    </App>
  </ConfigProvider>
</template>

<style lang="scss">
.bg-sidebar-deep::before {
  content: " ";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0.12;
  z-index: -1;
  background: url("/footer_lodyas.png");
}
.bg-sidebar-deep:where(.dark, .dark *)::before {
  opacity: 0.28;
}
.vben-normal-menu__item.is-active:where(.dark, .dark *) {
  // background-color: hsl(var(--primary)) !important;
  background-color: transparent !important;
  overflow: hidden;
}
.vben-normal-menu__item.is-active:where(.dark, .dark *)::before {
  content: " ";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0.45;
  z-index: -1;
  background-color: hsl(var(--primary)) !important;
}
</style>