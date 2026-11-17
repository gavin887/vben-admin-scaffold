<script lang="ts" setup>
import type { DataNode } from 'ant-design-vue/es/tree';
import { computed, ref } from 'vue';
import { useVbenDrawer, Tree } from '@vben/common-ui';
import { IconifyIcon } from '@vben/icons';
import { message, Spin } from 'ant-design-vue';
import { useVbenForm } from '#/adapter/form';
import { getAllMenusApi } from '#/api/core/menu';
import { createApi, editApi } from '#/api/system/role';

const formData = ref();

const [Form, formApi] = useVbenForm({
  schema: [
    {
      fieldName: 'admin_role_id',
      label: 'ID',
      defaultValue: 0,
      component: 'Input',
      dependencies: {
        show: false,
        // 随意一个字段改变时，都会触发
        triggerFields: [''],
      },
    },
    {
      fieldName: 'name',
      label: '名称',
      component: 'Input',
      componentProps: {
        placeholder: '请输入名称',
      },
      rules: 'required',
    },
    {
      fieldName: 'value',
      label: '值',
      component: 'Input',
      componentProps: {
        placeholder: '请输入值',
      },
      rules: 'required',
    },
    {
      fieldName: 'remark',
      label: '备注',
      component: 'Input',
      componentProps: {
        placeholder: '请输入备注',
      },
    },
    {
      fieldName: 'sort',
      label: '排序',
      defaultValue: 10000,
      component: 'InputNumber',
      rules: 'required',
    },
    {
      fieldName: 'status',
      label: '状态',
      defaultValue: 1,
      component: 'Switch',
      componentProps: {
        class: 'w-auto',
        checkedChildren: '启用',
        checkedValue: 1,
        unCheckedChildren: '停用',
        unCheckedValue: 0,
      },
    },
    {
      component: 'Input',
      fieldName: 'menu',
      label: '权限',
      modelPropName: 'modelLabel',
      formItemClass: 'items-start',
      labelClass: 'pt-2',
    },
  ],
  showDefaultActions: false,
});

const menuTrees = ref<DataNode[]>([]);
const loadingMenuTrees = ref(false);

const [Drawer, drawerApi] = useVbenDrawer({
  async onConfirm() {
    const { valid } = await formApi.validate();
    if (!valid) return;
    const values = await formApi.getValues();
    drawerApi.lock();
    (values.admin_role_id ? editApi(values) : createApi(values))
      .then((res) => {
        message.success({
          content: res.message,
        });
        drawerApi.close();
      })
      .catch(() => {
        drawerApi.unlock();
      });
  },
  onOpenChange(isOpen) {
    if (isOpen) {
      const data = drawerApi.getData();
      formApi.resetForm();
      if (data) {
        formData.value = data;
        formApi.setValues(data);
      }

      if (menuTrees.value.length === 0) {
        loadMenuTrees();
      }
    }
  },
  overlayBlur: 1,
});

async function loadMenuTrees() {
  loadingMenuTrees.value = true;
  try {
    const res = await getAllMenusApi();
    menuTrees.value = res as unknown as DataNode[];
  } finally {
    loadingMenuTrees.value = false;
  }
}

const getDrawerTitle = computed(() => {
  return formData.value?.admin_role_id ? '编辑角色' : '新增角色';
});
</script>
<template>
  <Drawer :title="getDrawerTitle">
    <Form>
      <template #menu="slotProps">
        <Spin :spinning="loadingMenuTrees" wrapper-class-name="w-full">
          <Tree
            :tree-data="menuTrees"
            multiple
            bordered
            :default-expanded-level="1"
            v-bind="slotProps"
            value-field="admin_menu_id"
            label-field="name"
            icon-field="meta.icon"
          >
            <template #node="{ value }">
              <IconifyIcon v-if="value.meta.icon" :icon="value.meta.icon" />
              {{ value.name }}
            </template>
          </Tree>
        </Spin>
      </template>
    </Form>
  </Drawer>
</template>
