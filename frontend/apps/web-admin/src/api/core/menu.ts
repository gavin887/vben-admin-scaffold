import type { RouteRecordStringComponent } from '@vben/types';

import { requestClient } from '#/api/request';

/**
 * 获取用户所有菜单
 */
export async function getAllMenusApi(status?: string) {
  return requestClient.get<RouteRecordStringComponent[]>('/menu/trees?status='+(status??'1'));
}
