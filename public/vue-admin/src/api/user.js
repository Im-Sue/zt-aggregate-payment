import request from '@/utils/request'

export function login(data) {
  return request({
    url: '/login/login',
    method: 'post',
    data
  })
}

export function getInfo() {
  return request({
    url: '/user/info',
    method: 'get'
  })
}

export function logout() {
  return request({
    url: '/user/logout',
    method: 'get'
  })
}

export function changePassword(data) {
  return request({
    url: '/user/changePassword',
    method: 'post',
    data
  })
}
