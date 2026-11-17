<script lang="ts" setup>
import { ref } from 'vue';
import { useVbenModal, Spinner } from '@vben/common-ui';
import { Pagination } from 'ant-design-vue';
import { IconifyIcon } from '@vben/icons';
import { imageListApi, uploadImgApi } from '#/api/upload';
import { message, Upload, Tooltip, Popover, Progress } from 'ant-design-vue';

const spinning = ref(false);
const percent = ref(0);

// 定义图片项的类型
interface ImageItem {
  url: string;
  id: number;
  filename: string;
  mimetype: string;
}

const formData = ref();
const imageList = ref<ImageItem[]>([]);
const result = ref<
  { id: number; uid: string; name: string; status: string; url: string }[]
>([]);
const current = ref(1);
const total = ref(0);
const getImageList = () => {
  imageListApi({ page: current.value, pageSize: 19 }).then((res) => {
    imageList.value = res.items;
    total.value = res.total;
  });
};

const checkImage = (item: ImageItem) => {
  let isCheck = false;
  for (let i = 0; i < result.value.length; i++) {
    if (result.value[i]?.url === item.url) {
      isCheck = true;
      break;
    }
  }
  return isCheck;
};
const selectImage = (item: ImageItem) => {
  if (checkImage(item)) {
    for (let i = 0; i < result.value.length; i++) {
      if (result.value[i]?.url === item.url) {
        result.value.splice(i, 1);
        break;
      }
    }
  } else {
    if (result.value.length >= formData.value.maxCount) {
      if (formData.value.maxCount == 1) {
        result.value = [];
      } else {
        message.error({
          content: '最多只能选择' + formData.value.maxCount + '张图片',
        });
        return false;
      }
    }
    result.value.push({
      id: item.id,
      uid: item.id + '',
      name: item.filename,
      status: 'done',
      url: item.url,
    });
  }
};
const imageLength = ref(0);
const complete = (type: string, msg: string = '') => {
  if (type == 'success') {
    message.success(msg || '上传成功');
  } else {
    message.error(msg || '上传失败');
  }
  imageLength.value--;
  refresh();
};
// 处理文件上传
const handleUpload = (file: File) => {
  imageLength.value++;
  spinning.value = true;
  try {
    uploadImgApi(
      { file },
      {
        onUploadProgress(event: any) {
          if (event.total)
            percent.value = Math.round((event.loaded * 100) / event.total);
        },
      },
    )
      .then((data: any) => {
        complete(data.code === 200 ? 'success' : 'error');
      })
      .catch(() => complete('error'));
    return false; // 阻止默认上传行为
  } catch (error) {
    complete('error');
    return false;
  }
};
const refresh = () => {
  if (imageLength.value == 0) {
    imageList.value = [];
    current.value = 1;
    total.value = 0;
    getImageList();
    spinning.value = false;
    percent.value = 0;
  }
};

const [Modal, modalApi] = useVbenModal({
  draggable: true, // 可拖拽
  centered: true, // 居中
  confirmDisabled: false,
  footer: true,
  contentClass: 'bg-background-deep',
  onOpenChange(isOpen) {
    if (isOpen) {
      const data = modalApi.getData();
      formData.value = data;
      current.value = 1;
      result.value = [];
      getImageList();
    }
  },
  onConfirm() {
    modalApi.sharedData = { payload: { imageList: result.value } };
    modalApi.close();
  },
  overlayBlur: 1,
});
</script>
<template>
  <Modal
    :title="formData?.btnText"
    class="w-full overflow-hidden sm:h-[80vh] sm:w-[716px]"
  >
    <div
      class="bg-card flex h-full flex-wrap content-start items-start justify-start gap-3 rounded-md p-4"
    >
      <div
        class="relative flex h-[120px] w-[120px] cursor-pointer items-center justify-center overflow-hidden bg-gray-200"
      >
        <Progress
          :percent="percent"
          :show-info="false"
          style="position: absolute; left: 0; top: -8px; right: 0; z-index: 200"
          v-show="spinning"
        />
        <Spinner
          :spinning="spinning"
          class="flex size-40 items-center justify-center"
        >
          <Upload
            ref="uploadRef"
            list-type="picture"
            :before-upload="handleUpload"
            :show-upload-list="false"
            :multiple="true"
          >
            <div
              class="flex h-[120px] w-[120px] flex-col items-center justify-center text-stone-600"
            >
              <IconifyIcon
                icon="ant-design:upload-outlined"
                class="mb-1 text-2xl"
              />
              上传
            </div>
          </Upload>
        </Spinner>
      </div>
      <div
        v-for="(item, index) in imageList"
        :key="index"
        class="h-[120px] w-[120px] cursor-pointer bg-gray-200 p-[5px]"
        :style="{
          backgroundColor: checkImage(item)
            ? 'hsl(var(--primary)) !important'
            : '',
        }"
        @click="selectImage(item)"
      >
        <Tooltip v-if="item.mimetype.indexOf('image') > -1">
          <template #title>{{ item.filename }}</template>
          <div
            class="flex h-[110px] w-[110px] items-center justify-center bg-gray-200"
          >
            <img :src="item.url" style="max-width: 110px; max-height: 110px" />
          </div>
        </Tooltip>
        <Popover v-if="item.mimetype.indexOf('video') > -1">
          <template #title>{{ item.filename }}</template>
          <template #content>
            <div class="flex h-full w-full items-center justify-center">
              <video
                :src="item.url"
                controls
                style="max-width: 360px; max-height: 360px"
              ></video>
            </div>
          </template>
          <div
            class="flex h-[110px] w-[110px] items-center justify-center bg-gray-200"
          >
            <video
              :src="item.url"
              style="max-width: 110px; max-height: 110px"
            ></video>
          </div>
        </Popover>
      </div>
    </div>
    <div class="mt-3 flex items-center justify-center">
      <Pagination
        v-model:current="current"
        :total="total"
        :pageSize="19"
        :hideOnSinglePage="true"
        @change="getImageList"
      />
    </div>
  </Modal>
</template>
