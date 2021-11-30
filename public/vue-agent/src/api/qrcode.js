import request from '@/utils/request'

export function getList(data) {
  return request({
    url: '/qrcode/getList',
    method: 'post',
    data
  })
}
export function getInfo(data) {
  return request({
    url: '/qrcode/getInfo',
    method: 'post',
    data
  })
}
export function saveInfo(data) {
  return request({
    url: '/qrcode/saveInfo',
    method: 'post',
    data
  })
}
export function del(data) {
  return request({
    url: '/qrcode/del',
    method: 'post',
    data
  })
}
export function speakerTest(data) {
  return request({
    url: '/qrcode/speakerTest',
    method: 'post',
    data
  })
}
