start:
	docker-compose up -d --build

close:
	docker-compose down

exec-app:
	docker-compose exec app sh