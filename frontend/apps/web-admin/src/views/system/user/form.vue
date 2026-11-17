<script lang="ts" setup>
import { computed, ref } from 'vue';
import { useVbenDrawer } from '@vben/common-ui';
import { message } from 'ant-design-vue';
import { useVbenForm } from '#/adapter/form';
import { createApi, editApi } from '#/api/system/user';
import { getTreesApi } from '#/api/system/dept';
import { getAllApi } from '#/api/system/role';

const formData = ref();

const [Form, formApi] = useVbenForm({
  schema: [
    {
      fieldName: 'admin_user_id',
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
        fieldName: 'avatar',
        component: 'ImageSelect',
        label: '头像',
        componentProps: {
          maxCount: 1,
        },
    },
    {
        fieldName: 'user_name',
        label: '用户名',
        component: 'Input',
        componentProps: {
            placeholder: '请输入用户名',
        },
        rules: 'required',
    },
    {
        fieldName: 'password',
        label: '密码',
        component: 'InputPassword',
        componentProps: {
            placeholder: '请输入密码',
        },
        dependencies: {
          if(values) {
            return values.admin_user_id?false:true;
          },
          // 随意一个字段改变时，都会触发
          triggerFields: ['admin_user_id'],
        },
        rules: 'required',
    },
    {
        fieldName: 'password',
        label: '新密码',
        component: 'InputPassword',
        componentProps: {
            placeholder: '请输入密码',
        },
        description: '不填写则不修改',
        controlClass: 'grow',
        wrapperClass: 'flex-wrap',
        formItemClass: 'items-start',
        labelClass: 'pt-[5px]',
        dependencies: {
          if(values) {
            return values.admin_user_id?true:false;
          },
          // 随意一个字段改变时，都会触发
          triggerFields: ['admin_user_id'],
        },
    },
    {
        fieldName: 'nick_name',
        label: '昵称',
        component: 'Input',
        componentProps: {
            placeholder: '请输入昵称',
        },
        rules: 'required',
    },
    {
      fieldName: 'dept_id',
      label: '部门',
      component: 'ApiTreeSelect',
      componentProps: {
        // 部门接口
        api: getTreesApi,
        // 部门接口转options格式
        labelField: 'dept_name',
        valueField: 'admin_dept_id',
        childrenField: 'children',
        class: 'w-[100%]',
        placeholder: '请选择部门',
        allowClear: false
      },
      rules: 'required',
    },
    {
      fieldName: 'role_id',
      label: '角色',
      component: 'ApiTreeSelect',
      componentProps: {
        // 部门接口
        api: getAllApi,
        // 部门接口转options格式
        labelField: 'name',
        valueField: 'admin_role_id',
        childrenField: 'children',
        class: 'w-[100%]',
        placeholder: '请选择角色',
        allowClear: false
      },
      rules: 'required',
    },
    {
        fieldName: 'email',
        label: '邮箱',
        component: 'Input',
        componentProps: {
            placeholder: '请输入邮箱',
        },
    },
    {
        fieldName: 'mobile',
        label: '手机号',
        component: 'Input',
        componentProps: {
            placeholder: '请输入手机号',
        },
    },
    {
        fieldName: 'gender',
        label: '性别',
        component: 'RadioGroup',
        defaultValue: 1,
        componentProps: {
          buttonStyle: 'solid',
          options: [
            { label: '男', value: 1 },
            { label: '女', value: 0 },
          ],
          optionType: 'button',
        },
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
  ],
  showDefaultActions: false,
});

const [Drawer, drawerApi] = useVbenDrawer({
  async onConfirm() {
    const { valid } = await formApi.validate();
    if (!valid) return;
    const values = await formApi.getValues();
    drawerApi.lock();
    (values.admin_user_id ? editApi(values) : createApi(values))
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
    }
  },
  overlayBlur: 1,
});

const getDrawerTitle = computed(() => {
  return formData.value?.id
    ? '编辑用户'
    : '新增用户';
});


</script>
<template>
  <Drawer :title="getDrawerTitle">
    <Form />
  </Drawer>
</template>
