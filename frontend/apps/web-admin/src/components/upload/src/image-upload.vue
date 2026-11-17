<script setup lang="ts">
import { Upload } from 'ant-design-vue';
import Sortable from 'sortablejs';
import { IconifyIcon } from '@vben/icons';
import type { UploadProps } from 'ant-design-vue';
import { message } from 'ant-design-vue';
import type { UploadRequestOption } from 'ant-design-vue/lib/vc-upload/interface';
import { ref, toRefs, watch, onMounted } from 'vue';
import { uploadImgApi } from '#/api/upload';

defineOptions({
  inheritAttrs: false,
});
const props = defineProps({
  value: {
    type: [String , Array],
    default: '',
  },
  maxCount: {
    type: Number,
    default: 1,
  },
  btnText: {
    type: String,
    default: '上传图片',
  },
});
const { maxCount,btnText } = toRefs(props);
const emit = defineEmits(['update:value']);
const fileList = ref<UploadProps['fileList']>([]);

watch(
    () => props.value,
    (v: any) => {
      let value: string[] = [];
      if (v) {
        if (typeof v == 'string') {
          value.push(v);
        } else {
          value = v;
        }
        fileList.value = value.map((item, i) => {
          if (item && typeof item == 'string') {
            return {
              uid: -i + '',
              name: item.substring(item.lastIndexOf('/') + 1),
              status: 'done',
              url: item,
            };
          } else if (item && typeof item == 'object') {
            return item;
          } else {
            return;
          }
        }) as UploadProps['fileList'];
      }else {
        fileList.value = [];
      }
    },
    {
      immediate: true,
      deep: true,
    },
);

function getValue() {
    const list = (fileList.value || [])
      .filter((item) => item?.status === 'done')
      .map((item: any) => {
        return {
          id: item?.response?.id || item?.id || -1,
          uid: item?.uid || item?.response?.id,
          name: item?.name || item?.response?.name,
          status: item?.status || item?.response?.status,
          url: item?.url || item?.response?.url
        };
      });
    return list;
}
const customRequest = (info: UploadRequestOption<any>) => {
  uploadImgApi({
    file: info.file,
  }, {
    onUploadProgress: function(progressEvent: any) {
      // 计算上传进度百分比
      let percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
      info.onProgress!({percent});
    }
  }).then((data: any) => {
    if(data.code == 200){
        info.onSuccess!(data);
        const value = getValue();
        emit('update:value', value);
        message.success({
          content: data.message,
        });
    }else{
        info.onError!(data);
        handleRemove(info.file);
        message.error({
          content: data.message,
        });
    }
  }).catch((data: any) => {
    info.onError!(data);
    handleRemove(info.file);
  });
};
const handleRemove = async (file: any) => {
    if (fileList.value) {
      const index = fileList.value.findIndex((item) => item.uid === file.uid);
      index !== -1 && fileList.value.splice(index, 1);
      const value = getValue();
      emit('update:value', value);
    }
};
const uploadRef = ref<InstanceType<typeof Upload> | null>(null);
onMounted(() => {
    initSortable();
});
function initSortable() {
  const el = uploadRef.value?.$el.querySelector('.ant-upload-list'); // 获取上传列表的DOM元素
  Sortable.create(el, {
    onEnd: ({ newIndex, oldIndex }) => {
      if (fileList.value && oldIndex!=undefined && newIndex!=undefined) {
        const movedItem = fileList.value.splice(oldIndex, 1)[0]; // 移除并保存被拖动的项
        if(movedItem != undefined){
          fileList.value.splice(newIndex, 0, movedItem); // 在新位置插入被拖动的项
          const value = getValue();
          emit('update:value', value);
        }
      }
    },
  });
}
</script>
<template>
  <div>
    <Upload
      ref="uploadRef"
      v-model:file-list="fileList"
      :custom-request="customRequest"
      :maxCount="maxCount"
      list-type="picture-card"
      @remove="handleRemove"
    >
      <div class="flex flex-col justify-center items-center" v-if="fileList && fileList.length < maxCount">
        <IconifyIcon icon="ant-design:plus-outlined" class="text-2xl mb-1" />
        {{ btnText }}
      </div>
    </Upload>
  </div>
</template>
