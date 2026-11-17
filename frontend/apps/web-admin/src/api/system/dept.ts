import { requestClient,requestClientBody } from '#/api/request';

/**
 * 获取部门列表
 */
export async function getAllApi(status?: string, dept_name?: string) {
  return requestClient.get('/dept/lists?status='+(status??'1')+'&dept_name='+(dept_name??''));
}
/**
 * 获取部门树
 */
export async function getTreesApi() {
  return requestClient.get('/dept/trees');
}

/**
 * 新增部门
 */
export async function createApi(data: any) {
  return requestClientBody.post('/dept/add', {form:data});
}

/**
 * 编辑部门
 */
export async function editApi(data: any) {
  return requestClientBody.post('/dept/edit?id='+data.admin_dept_id, {form:data});
}

/**
 * 更新状态
 */
export async function setstatusApi(data: any) {
  return requestClientBody.post('/dept/setstatus', data);
}

/**
 * 删除部门
 */
export async function deleteApi(id: number) {
  return requestClientBody.post('/dept/delete?id='+id);
}
