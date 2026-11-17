<script lang="ts" setup>
import type { VbenFormProps } from '#/adapter/form';
import type { VxeGridProps } from '#/adapter/vxe-table';
import { Page,useVbenDrawer } from '@vben/common-ui';
import { Button,Popover,Avatar,Image,Switch,Popconfirm,message } from 'ant-design-vue';
import { IconifyIcon } from '@vben/icons';
import { useVbenVxeGrid } from '#/adapter/vxe-table';
import { getListApi,deleteApi,setstatusApi } from '#/api/system/user';
import { getTreesApi } from '#/api/system/dept';
import Form from './form.vue';

const formOptions: VbenFormProps = {
  // 默认展开
  collapsed: false,
  schema: [
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
        allowClear: true
      },
    },
    {
      component: 'Input',
      componentProps: {
        allowClear: true,
        placeholder: '请输入用户名/昵称/手机号',
      },
      fieldName: 'name',
      label: '关键词',
      defaultValue: '',
    },
    {
      component: 'Select',
      componentProps: {
        allowClear: true,
        options: [
          {
            label: '全部',
            value: -1,
          },
          {
            label: '启用',
            value: 1,
          },
          {
            label: '停用',
            value: 0,
          },
        ],
        placeholder: '请选择用户状态',
      },
      fieldName: 'status',
      label: '状态',
      defaultValue: -1,
    },
  ],
  // 控制表单是否显示折叠按钮
  showCollapseButton: false,
  // 是否在字段值改变时提交表单
  submitOnChange: true,
  // 按下回车时是否提交表单
  submitOnEnter: true,
};

interface RowType {
  admin_user_id: number;
  user_name: string;
  mobile: string;
  nick_name: string;
  avatar: Array<{ url: string, id: number }>;
  gender: number;
  email: string;
  role_id: number;
  dept_id: number;
  status: number;
  create_time: string;
  update_time: string;
}

const gridOptions: VxeGridProps<RowType> = {
  columns: [
    { field: 'admin_user_id', title: 'ID', minWidth:'auto' },
    { field: 'avatar', slots: { default: 'avatar' }, title: '头像', minWidth:'auto' },
    { field: 'user_name', title: '用户名', minWidth:'auto' },
    { field: 'nick_name', title: '昵称', minWidth:'auto' },
    { field: 'mobile', title: '手机号', minWidth:'auto' },
    { field: 'role.name', title: '角色', minWidth:'auto' },
    { field: 'dept.dept_name', title: '部门', minWidth:'auto' },
    { field: 'status', slots: { default: 'status' }, title: '状态', minWidth:'auto' },
    { field: 'create_time', title: '创建时间', minWidth:'auto' },
    { field: 'action', fixed: 'right', slots: { default: 'action' }, title: '操作', minWidth:'auto' },
  ],
  showOverflow: false,
  exportConfig: {},
  keepSource: true,
  // border: true,
  proxyConfig: {
    ajax: {
      query: async ({ page }, formValues) => {
        return await getListApi({
            page: page.currentPage,
            pageSize: page.pageSize,
            ...formValues
        });
      },
    },
    sort: true,
  },
  toolbarConfig: {
    custom: true,
    // export: true,
    // import: true,
    refresh: { code: 'query' },
    zoom: true,
  },
  rowConfig: {
    keyField: 'admin_user_id',//主键的字段名
  },
};

const [Grid, gridApi] = useVbenVxeGrid({ formOptions,gridOptions });

const [FormDrawer, formDrawerApi] = useVbenDrawer({
  connectedComponent: Form,
  onBeforeClose() {
    gridApi.query();
    return true;
  },
});
function openFormDrawer(item={admin_user_id:0}) {
  formDrawerApi
    .setData(item)
    .open();
}
function confirmDel(id: number) {
  gridApi.setLoading(true);
  deleteApi(id).then((res) => {
    gridApi.setLoading(false);
    message.success({
      content: res.message,
    });
    gridApi.query();
  });
}
function setStatus(id: number,status: number) {
    setstatusApi({id,status}).then((res) => {
      message.success({
          content: res.message,
      });
    });
}
</script>

<template>
  <Page>
    <Grid table-title="用户列表">
      <template #toolbar-tools>
        <Button class="mr-2" type="primary" shape="round" @click="openFormDrawer()"> <div class="flex items-center justify-center"><IconifyIcon class="size-4 cursor-pointer outline-none mr-1" icon="ant-design:plus-circle-filled" />新增用户</div> </Button>
      </template>
      <template #avatar="{ row }">
        <Popover>
          <template #content>
            <Image :width="90" :src="item.url" v-for="(item,index) in row.avatar" :key="index" />
          </template>
          <Avatar :size="32" :src="item.url" v-for="(item,index) in row.avatar" :key="index"></Avatar>
        </Popover>
      </template>
      <template #status="{ row }">
        <Switch v-model:checked="row.status" @change="setStatus(row.admin_user_id,row.status)" checkedChildren="启用" :checkedValue="1" unCheckedChildren="停用" :unCheckedValue="0" />
      </template>
      <template #action="{ row }">
        <Button type="link" class="px-2" @click="openFormDrawer(row)"><IconifyIcon class="size-4 cursor-pointer outline-none" icon="ant-design:edit-outlined" /></Button>
        <Button type="link" class="px-0 mx-2" danger>
          <popconfirm title="确认删除此用户?" ok-text="确认" cancel-text="取消" placement="left" @confirm="confirmDel(row.admin_user_id)">
            <template #icon>
              <IconifyIcon class="size-4 text-red-600 outline-none inline" icon="ant-design:question-circle-outlined" />
            </template>
            <IconifyIcon class="size-4 cursor-pointer outline-none" icon="ant-design:delete-outlined" />
          </popconfirm>
        </Button>
      </template>
    </Grid>
    <FormDrawer />
  </Page>
</template>
