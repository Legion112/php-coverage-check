image.build:
	docker build . -f ./build/docker/Dockerfile -t coverage-check --no-cache
image.publish:
	docker build . -f ./build/docker/Dockerfile -t fearofcode/php-coverage-check:$(version) --no-cache
	docker push fearofcode/php-coverage-check:$(version)