<script lang="ts" setup>
import { message } from 'ant-design-vue';
import { refAutoReset } from '@vueuse/core';
import { Page, Spinner } from '@vben/common-ui';
import { useVbenForm } from '#/adapter/form';
import { getProfileApi, updateProfileApi } from '#/api/core/user';

const spinning = refAutoReset(false, 5000);

const [Form, formApi] = useVbenForm({
  handleSubmit: onSubmit,
  schema: [
    {
      fieldName: 'avatar',
      component: 'ImageSelect',
      label: '头像',
      componentProps: {
        maxCount: 1,
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
      fieldName: 'mobile',
      label: '手机号',
      component: 'Input',
      componentProps: {
        placeholder: '请输入手机号',
      },
    },
    {
      fieldName: 'password',
      label: '新密码',
      component: 'InputPassword',
      componentProps: {
        placeholder: '请输入新密码',
      },
      description: '不填写则不修改',
      controlClass: 'grow',
      wrapperClass: 'flex-wrap',
      formItemClass: 'items-start',
      labelClass: 'pt-[5px]',
    },
  ],
  showDefaultActions: true,
  actionWrapperClass: 'text-left pl-[100px]',
  actionButtonsReverse: true,
});

getProfileApi().then((res) => {
  formApi.setValues(res);
});
function onSubmit(values: Record<string, any>) {
  spinning.value = true;
  updateProfileApi(values).then((res) => {
    message.success({
      content: res.message,
    });
    spinning.value = false;
  });
}
</script>
<template>
  <Page>
    <div class="bg-card rounded-md p-5">
      <div class="pb-5 text-[1rem]">个人中心</div>
      <div class="lg:w-[500px]">
        <Spinner :spinning="spinning">
          <Form />
        </Spinner>
      </div>
    </div>
  </Page>
</template>
