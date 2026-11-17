import { requestClient,requestClientBody } from '#/api/request';

/**
 * 获取菜单列表
 */
export async function getAllMenusApi(status?: string, name?: string) {
  return requestClient.get('/menu/lists?status='+(status??'1')+'&name='+(name??''));
}
/**
 * 获取菜单树
 */
export async function getTreesApi() {
  return requestClient.get('/menu/trees');
}

/**
 * 新增菜单
 */
export async function createApi(data: any) {
  return requestClientBody.post('/menu/add', {form:data});
}

/**
 * 编辑菜单
 */
export async function editApi(data: any) {
  return requestClientBody.post('/menu/edit?id='+data.admin_menu_id, {form:data});
}

  /**
 * 更新状态
 */
export async function setstatusApi(data: any) {
  return requestClientBody.post('/menu/setstatus', data);
}

/**
 * 删除菜单
 */
export async function deleteApi(id: number) {
  return requestClientBody.post('/menu/delete?id='+id);
}
