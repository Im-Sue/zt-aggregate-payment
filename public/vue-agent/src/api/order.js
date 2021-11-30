import request from '@/utils/request'

export function getList(data) {
  return request({
    url: '/order/getList',
    method: 'post',
    data
  })
}
export function getTongji(data) {
  return request({
    url: '/order/getTongji',
    method: 'post',
    data
  })
}
export function getShopList(data) {
  return request({
    url: '/order/getShopList',
    method: 'post',
    data
  })
}
export function getQrcodeList(data) {
  return request({
    url: '/order/getQrcodeList',
    method: 'post',
    data
  })
}

export function refund(data) {
  return request({
    url: '/order/refund',
    method: 'post',
    data
  })
}
