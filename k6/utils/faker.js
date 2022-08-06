import { Faker } from 'k6/x/faker'

const faker = new Faker(Math.floor(100000000000000000 * Math.random()))

export { faker }