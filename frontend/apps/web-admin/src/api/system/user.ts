import { requestClient,requestClientBody } from '#/api/request';

/**
 * 获取用户列表
 */
export async function getListApi(params: any) {
  let searchParams = new URLSearchParams(params);
  return requestClient.get('/admin/lists?'+searchParams.toString());
}

/**
 * 新增用户
 */
export async function createApi(data: any) {
  return requestClientBody.post('/admin/add', {form:data});
}

/**
 * 编辑用户
 */
export async function editApi(data: any) {
  return requestClientBody.post('/admin/edit?id='+data.admin_user_id, {form:data});
}

/**
 * 更新状态
 */
export async function setstatusApi(data: any) {
  return requestClientBody.post('/admin/setstatus', data);
}

/**
 * 删除用户
 */
export async function deleteApi(id: number) {
  return requestClientBody.post('/admin/delete?id='+id);
}

/**
 * 用户详情
 */
export async function detailApi() {
  return requestClient.get('/admin/detail');
}