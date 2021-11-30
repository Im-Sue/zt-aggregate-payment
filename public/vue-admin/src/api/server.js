import request from '@/utils/request'

export function getList(data) {
  return request({
    url: '/server/getList',
    method: 'post',
    data
  })
}
export function getInfo(data) {
  return request({
    url: '/server/getInfo',
    method: 'post',
    data
  })
}
export function saveInfo(data) {
  return request({
    url: '/server/saveInfo',
    method: 'post',
    data
  })
}
export function del(data) {
  return request({
    url: '/server/del',
    method: 'post',
    data
  })
}

export function uploadPem(data) {
  return request({
    url: '/upload/pem',
    method: 'post',
    data
  })
}
