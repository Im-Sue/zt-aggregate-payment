import request from '@/utils/request'

export function getList(data) {
  return request({
    url: '/agent/getList',
    method: 'post',
    data
  })
}
export function getInfo(data) {
  return request({
    url: '/agent/getInfo',
    method: 'post',
    data
  })
}
export function saveInfo(data) {
  return request({
    url: '/agent/saveInfo',
    method: 'post',
    data
  })
}
export function del(data) {
  return request({
    url: '/agent/del',
    method: 'post',
    data
  })
}
export function getAgentLoginUrl(data) {
  return request({
    url: '/agent/getLoginUrl',
    method: 'post',
    data
  })
}
