<script lang="ts" setup>
import { useVbenDrawer } from '@vben/common-ui';
import { useVbenForm } from '#/adapter/form';
import { message } from 'ant-design-vue';
import { getTreesApi, createApi, editApi } from '#/api/system/dept';

defineOptions({
  name: 'FormDrawerDemo',
});

const [Form, formApi] = useVbenForm({
  handleSubmit: onSubmit,
  schema: [
    {
      fieldName: 'admin_dept_id',
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
      label: '上级部门',
      component: 'ApiTreeSelect',
      componentProps: {
        afterFetch: (data: { dept_name: string; admin_dept_id: number }[]) => {
          return [{ dept_name: '顶级部门', admin_dept_id: 0 }, ...data];
        },
        api: getTreesApi,
        // 部门接口转options格式
        labelField: 'dept_name',
        valueField: 'admin_dept_id',
        childrenField: 'children',
        class: 'w-[100%]',
        placeholder: '请选择上级部门',
        allowClear: true,
      },
      rules: 'required',
    },
    {
      fieldName: 'dept_name',
      label: '名称',
      component: 'Input',
      componentProps: {
        placeholder: '请输入名称',
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
      component: 'Switch',
      componentProps: {
        class: 'w-auto',
        checkedChildren: '启用',
        checkedValue: 1,
        unCheckedChildren: '停用',
        unCheckedValue: 0,
      },
    },
  ],
  showDefaultActions: false,
});
function onSubmit(values: Record<string, any>) {
  (values.admin_dept_id ? editApi(values) : createApi(values))
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
      if (values.admin_dept_id) {
        formApi.setValues(values);
        drawerApi.setState({ title: '编辑部门' });
      } else {
        formApi.setValues({
          pid: 0,
          status: 1,
        });
        drawerApi.setState({ title: '新增部门' });
      }
    }
  },
  title: '新增部门',
  overlayBlur: 1,
});
</script>
<template>
  <Drawer>
    <Form />
  </Drawer>
</template>
