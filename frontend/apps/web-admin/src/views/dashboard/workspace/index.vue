<script lang="ts" setup>
import { WorkbenchHeader } from '@vben/common-ui';
import { preferences } from '@vben/preferences';
import { useUserStore } from '@vben/stores';
import { Card } from 'ant-design-vue';

const userStore = useUserStore();
</script>

<template>
  <div class="p-5">
    <WorkbenchHeader
      :avatar="userStore.userInfo?.avatar || preferences.app.defaultAvatar"
    >
      <template #title
        >欢迎，{{
          userStore.userInfo?.realName || userStore.userInfo?.username
        }}！</template
      >
      <template #description>通用管理后台</template>
    </WorkbenchHeader>
    <Card class="mt-5" title="工作台">
      <p>从左侧菜单进入您有权限访问的功能。</p>
    </Card>
  </div>
</template>
