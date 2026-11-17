<script lang="ts" setup>
import type { VbenFormProps } from '#/adapter/form';
import type { VxeGridProps } from '#/adapter/vxe-table';
import { Page,useVbenDrawer } from '@vben/common-ui';
import { Button,Switch,Popconfirm,message } from 'ant-design-vue';
import { IconifyIcon } from '@vben/icons';
import { useVbenVxeGrid } from '#/adapter/vxe-table';
import { getListApi,deleteApi,setstatusApi,editApi } from '#/api/system/role';
import Form from './form.vue';

const formOptions: VbenFormProps = {
  // 默认展开
  collapsed: false,
  schema: [
    {
      component: 'Input',
      componentProps: {
        allowClear: true,
        placeholder: '请输入角色名称',
      },
      fieldName: 'name',
      label: '角色名称',
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
        placeholder: '请选择角色状态',
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
  admin_role_id: number;
  name: string;
  value: string;
  sort: number;
  old_sort: number;
  status: number;
  remark: string;
  create_time: string;
}

const gridOptions: VxeGridProps<RowType> = {
  columns: [
    { field: 'name', title: '角色名称', treeNode: true, minWidth:'auto' },
    { field: 'value', title: '值', minWidth:'auto' },
    { field: 'sort', title: '排序', editRender: { name: 'VxeNumberInput', props: { type: 'integer' } }, minWidth:'auto' },
    { field: 'status', slots: { default: 'status' }, title: '状态', minWidth:'auto' },
    { field: 'remark', title: '备注', minWidth:'auto' },
    { field: 'create_time', title: '创建时间', minWidth:'auto' },
    { field: 'action', fixed: 'right', slots: { default: 'action' }, title: '操作', minWidth:'auto' },
  ],
  editConfig: {
    mode: 'cell',
    trigger: 'click',
  },
  showOverflow: false,
  exportConfig: {},
  keepSource: true,
  proxyConfig: {
    ajax: {
      query: async ({ page }, formValues) => {
        return await getListApi({
            page: page.currentPage,
            pageSize: page.pageSize,
            ...formValues
        }).then((res) => {
          res.items.forEach((item: any) => {
            item.old_sort = item.sort;
          });
          return res;
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
    keyField: 'admin_role_id',//主键的字段名
  },
};

const [Grid, gridApi] = useVbenVxeGrid({ formOptions,gridOptions,gridEvents:{
  editClosed: (params : any) => {
    if(params.row.sort != params.row.old_sort){
      editApi({admin_role_id:params.row.admin_role_id,sort:params.row.sort}).then((res) => {
        message.success({content: res.message  ?? '操作成功'});
        gridApi.query();
      })
    }
  }
} });

const [FormDrawer, formDrawerApi] = useVbenDrawer({
  connectedComponent: Form,
  onBeforeClose() {
    gridApi.query();
    return true;
  },
});
function openFormDrawer(item={admin_role_id:0}) {
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
    <Grid table-title="角色列表">
      <template #toolbar-tools>
        <Button class="mr-2" type="primary" shape="round" @click="openFormDrawer()"> <div class="flex items-center justify-center"><IconifyIcon class="size-4 cursor-pointer outline-none mr-1" icon="ant-design:plus-circle-filled" />新增角色</div> </Button>
      </template>
      <template #status="{ row }">
        <Switch v-model:checked="row.status" @change="setStatus(row.admin_role_id,row.status)" checkedChildren="启用" :checkedValue="1" unCheckedChildren="停用" :unCheckedValue="0" />
      </template>
      <template #action="{ row }">
        <Button type="link" class="px-2" @click="openFormDrawer(row)"><IconifyIcon class="size-4 cursor-pointer outline-none" icon="ant-design:edit-outlined" /></Button>
        <Button type="link" class="px-0 mx-2" danger>
          <popconfirm title="确认删除此角色?" ok-text="确认" cancel-text="取消" placement="left" @confirm="confirmDel(row.admin_role_id)">
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
