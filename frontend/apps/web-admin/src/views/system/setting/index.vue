<script lang="ts" setup>
import { Page } from '@vben/common-ui';
import { Card, Descriptions, DescriptionsItem } from 'ant-design-vue';
</script>

<template>
  <Page title="系统设置">
    <Card title="文件存储">
      <Descriptions bordered :column="1">
        <DescriptionsItem label="存储方式">本地存储</DescriptionsItem>
        <DescriptionsItem label="文件访问"
          >上传的文件由当前后台服务提供访问。</DescriptionsItem
        >
      </Descriptions>
    </Card>
  </Page>
</template>
