image.build:
	docker build . -f ./build/docker/Dockerfile -t coverage-check --no-cache
image.publish:
	docker buildx build . --push --platform linux/arm/v7,linux/arm64/v8,linux/amd64  --tag fearofcode/php-coverage-check:$(version) -f ./build/docker/Dockerfile