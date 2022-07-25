FROM composer:2 as build

COPY ./ /build/
WORKDIR /build
RUN composer i --no-dev


FROM php:8.1-cli-alpine

COPY --from=build /build/ /coverage-check/


ENTRYPOINT ["/coverage-check/bin/entrypoint"]