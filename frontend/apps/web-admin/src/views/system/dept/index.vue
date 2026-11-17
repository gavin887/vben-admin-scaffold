<script lang="ts" setup>
import type { VbenFormProps } from '#/adapter/form';
import type { VxeGridProps } from '#/adapter/vxe-table';
import { Page,useVbenDrawer } from '@vben/common-ui';
import { Button,Switch,Popconfirm,message } from 'ant-design-vue';
import { IconifyIcon } from '@vben/icons';
import { useVbenVxeGrid } from '#/adapter/vxe-table';
import { getAllApi,deleteApi,setstatusApi,editApi } from '#/api/system/dept';
import { ref } from 'vue';
import Form from './form.vue';

const formOptions: VbenFormProps = {
  // 默认展开
  collapsed: false,
  schema: [
    {
      component: 'Input',
      fieldName: 'dept_name',
      label: '部门名称',
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
        placeholder: '请选择部门状态',
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
  admin_dept_id: number;
  pid: number;
  dept_name: string;
  sort: number;
  old_sort: number;
  remark: string;
  status: number;
  create_time: string;
}

const isExpandAll = ref(true);
// const isExpandAll = ref(gridApi.useStore((state) => state.gridOptions?.treeConfig?.expandAll));

const gridOptions: VxeGridProps<RowType> = {
  columns: [
    { field: 'dept_name', title: '部门名称', treeNode: true, minWidth:'auto' },
    { field: 'remark', title: '备注', minWidth:'auto' },
    { field: 'sort', title: '排序', editRender: { name: 'VxeNumberInput', props: { type: 'integer' } }, minWidth:'auto' },
    { field: 'status', slots: { default: 'status' }, title: '状态', minWidth:'auto' },
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
      query: async ({}, formValues) => {
        return await getAllApi(formValues.status, formValues.dept_name).then((res) => {
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
  pagerConfig: {
    enabled: false,
  },
  treeConfig: {
    parentField: 'pid',
    rowField: 'admin_dept_id',
    transform: true,
    expandAll: isExpandAll.value,
    reserve: true,//是否保留展开状态，对于某些场景可能会用到，比如数据被刷新之后还保留之前展开的状态（需要有 rowConfig.keyField）
  },
  rowConfig: {
    keyField: 'admin_dept_id',//主键的字段名(用于配合treeConfig.reserve)
  },
};

const [Grid, gridApi] = useVbenVxeGrid({ formOptions,gridOptions,gridEvents:{
  editClosed: (params : any) => {
    if(params.row.sort != params.row.old_sort){
      editApi({admin_dept_id:params.row.admin_dept_id,sort:params.row.sort}).then((res) => {
        message.success({content: res.message  ?? '操作成功'});
        gridApi.query();
      })
    }
  }
} });

const expandAll = () => {
  isExpandAll.value = !isExpandAll.value;
  gridApi.grid?.setAllTreeExpand(isExpandAll.value);
};

const [FormDrawer, formDrawerApi] = useVbenDrawer({
  connectedComponent: Form,
  onBeforeClose() {
    gridApi.query();
    return true;
  },
});
function openFormDrawer(item={admin_dept_id:0}) {
  formDrawerApi
    .setData({values:item})
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
    <Grid table-title="部门列表">
      <template #toolbar-tools>
        <Button class="mr-2" type="link" @click="expandAll"> <div class="flex items-center justify-center"><IconifyIcon class="size-4 cursor-pointer outline-none" :icon="'ant-design:caret-'+(isExpandAll?'up':'down')+'-filled'" />{{isExpandAll?'折叠全部':'展开全部'}}</div> </Button>
        <Button class="mr-2" type="primary" @click="openFormDrawer()"> <div class="flex items-center justify-center"><IconifyIcon class="size-4 cursor-pointer outline-none mr-1" icon="ant-design:plus-circle-filled" />新增部门</div> </Button>
      </template>
      <template #status="{ row }">
        <Switch v-model:checked="row.status" @change="setStatus(row.admin_dept_id,row.status)" checkedChildren="启用" :checkedValue="1" unCheckedChildren="停用" :unCheckedValue="0" />
      </template>
      <template #action="{ row }">
        <Button type="link" class="px-2" @click="openFormDrawer(row)"><IconifyIcon class="size-4 cursor-pointer outline-none" icon="ant-design:edit-outlined" /></Button>
        <Button type="link" class="px-0 mx-2" danger>
          <popconfirm title="确认删除此部门?" ok-text="确认" cancel-text="取消" placement="left" @confirm="confirmDel(row.admin_dept_id)">
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
