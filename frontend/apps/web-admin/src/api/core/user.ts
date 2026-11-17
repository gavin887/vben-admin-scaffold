import type { UserInfo } from '@vben/types';

import { requestClient } from '#/api/request';

/**
 * 获取用户信息
 */
export async function getUserInfoApi() {
  return requestClient.get<UserInfo>('/auth/info');
}

export async function getProfileApi() {
  return requestClient.get('/auth/profile');
}

export async function updateProfileApi(form: Record<string, any>) {
  return requestClient.post(
    '/auth/profile',
    { form },
    { responseReturn: 'body' },
  );
}
