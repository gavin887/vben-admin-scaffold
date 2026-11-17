import { requestClient, requestClientBody } from '#/api/request';

export async function uploadImgApi(file: any, config?: any) {
  return requestClientBody.upload('/upload/img', file, config);
}

export async function uploadFileApi(file: any, config?: any) {
  return requestClientBody.upload('/upload/file', file, config);
}

export async function imageListApi(params: any) {
  return requestClient.get(`/upload/imagelist?${new URLSearchParams(params)}`);
}
