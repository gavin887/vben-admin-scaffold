import { requestClient,requestClientBody } from '#/api/request';

/**
 * 获取角色列表
 */
export async function getListApi(params: any) {
  let searchParams = new URLSearchParams(params);
  return requestClient.get('/role/lists?'+searchParams.toString());
}
/**
 * 获取用于表单的所有角色
 */
export async function getAllApi() {
  return requestClient.get('/role/all');
}

/**
 * 新增角色
 */
export async function createApi(data: any) {
  return requestClientBody.post('/role/add', {form:data});
}

/**
 * 编辑角色
 */
export async function editApi(data: any) {
  return requestClientBody.post('/role/edit?id='+data.admin_role_id, {form:data});
}

/**
 * 更新状态
 */
export async function setstatusApi(data: any) {
  return requestClientBody.post('/role/setstatus', data);
}

/**
 * 删除角色
 */
export async function deleteApi(id: number) {
  return requestClientBody.post('/role/delete?id='+id);
}
