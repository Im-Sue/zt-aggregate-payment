import request from '@/utils/request'

export function getTongji() {
  return request({
    url: '/index/getTongji',
    method: 'get'
  })
}
export function getChartData() {
  return request({
    url: '/index/getChartData',
    method: 'get'
  })
}
