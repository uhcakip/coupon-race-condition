import http from 'k6/http'
import { check } from 'k6'
import { faker } from '../utils/faker.js'

export const options = {
  /*
  stages: [
    {duration: '30s', target: '50'},
    {duration: '30s', target: '200'},
    {duration: '30s', target: '300'},
    {duration: '30s', target: '400'},
    {duration: '30s', target: '500'},
  ],
  */

  vus: 700,
  duration: '1s',
  // iterations: 1,
  thresholds: {
    'http_req_duration{status:200}': ['max>=0'],
    'http_req_duration{status:400}': ['max>=0'],
    'http_req_duration{status:423}': ['max>=0'],
    'http_req_duration{status:429}': ['max>=0'],
    'http_req_duration{status:500}': ['max>=0'],
  },
  summaryTrendStats: ['min', 'med', 'avg', 'p(90)', 'p(95)', 'max', 'count'],
}

export default function () {
  const url = 'http://nginx/api/member/sign-up'
  const headers = {
    'Content-Type': 'application/json; charset=utf-8',
  }
  const body = {
    phone: '886' + faker.phone().substring(1),
    password: '123qwe',
  }

  const response = http.post(url, JSON.stringify(body), { headers })
  check(response, {
    'Success': (response) => response.status === 200
  })

}