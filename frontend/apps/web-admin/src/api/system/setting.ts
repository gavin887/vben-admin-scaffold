import { requestClient, requestClientBody } from '#/api/request';

/**
 * 读取设置分组 (如 key=mail) -> data:{ values:{...} }
 */
export async function getSettingDetailApi(key: string) {
  return requestClient.get(`/setting/detail?key=${key}`);
}

/**
 * 保存设置分组 (整体覆盖 values)
 */
export async function editSettingApi(key: string, form: Record<string, any>) {
  return requestClientBody.post(`/setting/edit?key=${key}`, { form });
}
