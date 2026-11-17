<script setup lang="ts">
import { Upload,Button } from 'ant-design-vue';
import { IconifyIcon } from '@vben/icons';
import type { UploadProps } from 'ant-design-vue';
import { message } from 'ant-design-vue';
import type { UploadRequestOption } from 'ant-design-vue/lib/vc-upload/interface';
import { ref, toRefs, watch } from 'vue';
import { uploadFileApi } from '#/api/upload';

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
  engine: {
    type: String,
    default: '',
  },
});
const { maxCount,engine } = toRefs(props);
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
  uploadFileApi({
    file: info.file,
    engine: engine.value
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
</script>
<template>
  <div>
    <Upload
      v-model:file-list="fileList"
      :custom-request="customRequest"
      :maxCount="maxCount"
      @remove="handleRemove"
    >
      <Button>
        <IconifyIcon icon="ant-design:upload-outlined" class="text-2xl mb-1" />
        上传文件
      </Button>
    </Upload>
  </div>
</template>
