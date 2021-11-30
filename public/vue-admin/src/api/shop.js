import request from '@/utils/request'

export function getList(data) {
  return request({
    url: '/shop/getList',
    method: 'post',
    data
  })
}
export function getInfo(data) {
  return request({
    url: '/shop/getInfo',
    method: 'post',
    data
  })
}
export function saveInfo(data) {
  return request({
    url: '/shop/saveInfo',
    method: 'post',
    data
  })
}
export function del(data) {
  return request({
    url: '/shop/del',
    method: 'post',
    data
  })
}
export function getPayInfo(data) {
  return request({
    url: '/shop/getPayInfo',
    method: 'post',
    data
  })
}
export function savePayInfo(data) {
  return request({
    url: '/shop/savePayInfo',
    method: 'post',
    data
  })
}
export function getServerList(data) {
  return request({
    url: '/shop/getServerList',
    method: 'post',
    data
  })
}
export function getAgentList(data) {
  return request({
    url: '/shop/getAgentList',
    method: 'post',
    data
  })
}

