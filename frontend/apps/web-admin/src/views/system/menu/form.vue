<script lang="ts" setup>
import { useVbenDrawer } from '@vben/common-ui';
import { useVbenForm } from '#/adapter/form';
import { message } from 'ant-design-vue';
import { getTreesApi, createApi, editApi } from '#/api/system/menu';

defineOptions({
  name: 'FormDrawerDemo',
});

const [Form, formApi] = useVbenForm({
  handleSubmit: onSubmit,
  schema: [
    {
      fieldName: 'admin_menu_id',
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
      fieldName: 'pid',
      label: '上级菜单',
      component: 'ApiTreeSelect',
      componentProps: {
        // 菜单接口转options格式
        afterFetch: (data: { name: string; admin_menu_id: number }[]) => {
          return [{ name: '顶级菜单', admin_menu_id: 0 }, ...data];
        },
        // 菜单接口
        api: getTreesApi,
        // 菜单接口转options格式
        labelField: 'name',
        valueField: 'admin_menu_id',
        childrenField: 'children',
        class: 'w-[100%]',
        placeholder: '请选择上级菜单',
        allowClear: true,
      },
      rules: 'required',
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
      fieldName: 'path',
      label: '路径',
      component: 'Input',
      componentProps: {
        placeholder: '请输入路径',
      },
      rules: 'required',
    },
    {
      fieldName: 'component',
      label: '组件',
      component: 'Input',
      componentProps: {
        placeholder: '请输入组件路径',
      },
    },
    {
      fieldName: 'redirect',
      label: '重定向',
      component: 'Input',
      componentProps: {
        placeholder: '请输入重定向路径',
      },
    },
    {
      fieldName: 'meta.title',
      label: '标题',
      component: 'Input',
      componentProps: {
        placeholder: '请输入标题',
      },
      rules: 'required',
    },
    {
      fieldName: 'meta.icon',
      label: '图标',
      component: 'IconPicker',
      componentProps: {
        placeholder: '请选择图标',
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
      fieldName: 'meta.hideMenu',
      label: '不在菜单上显示',
      labelWidth: 150,
      component: 'Switch',
      componentProps: {
        class: 'w-auto',
        checkedChildren: '是',
        unCheckedChildren: '否',
      },
    },
    {
      fieldName: 'meta.hideChildrenInMenu',
      label: '隐藏子菜单',
      labelWidth: 150,
      component: 'Switch',
      componentProps: {
        class: 'w-auto',
        checkedChildren: '是',
        unCheckedChildren: '否',
      },
    },
    {
      fieldName: 'meta.hideBreadcrumb',
      label: '不在面包屑上显示',
      labelWidth: 150,
      component: 'Switch',
      componentProps: {
        class: 'w-auto',
        checkedChildren: '是',
        unCheckedChildren: '否',
      },
    },
    {
      fieldName: 'meta.currentActiveMenu',
      label: '当前激活的菜单',
      labelWidth: 150,
      component: 'Input',
      componentProps: {
        placeholder: '请输入当前激活菜单路径',
      },
    },
  ],
  showDefaultActions: false,
});
function onSubmit(values: Record<string, any>) {
  (values.admin_menu_id ? editApi(values) : createApi(values))
    .then((res) => {
      message.success({
        content: res.message,
      });
      drawerApi.close();
    })
    .catch(() => {
      drawerApi.unlock();
    });
}
const [Drawer, drawerApi] = useVbenDrawer({
  onCancel() {
    drawerApi.close();
  },
  onConfirm: async () => {
    await formApi.validateAndSubmitForm();
    drawerApi.lock();
  },
  onOpenChange(isOpen: boolean) {
    if (isOpen) {
      formApi.resetForm();
      const { values } = drawerApi.getData<Record<string, any>>();
      if (values.admin_menu_id) {
        formApi.setValues(values);
        drawerApi.setState({ title: '编辑菜单' });
      } else {
        formApi.setValues({
          pid: 0,
          status: 1,
          meta: {
            title: '',
            icon: '',
            hideMenu: false,
            hideChildrenInMenu: false,
            hideBreadcrumb: false,
            currentActiveMenu: '',
          },
        });
        drawerApi.setState({ title: '新增菜单' });
      }
    }
  },
  title: '新增菜单',
  overlayBlur: 1,
});
</script>
<template>
  <Drawer>
    <Form />
  </Drawer>
</template>
