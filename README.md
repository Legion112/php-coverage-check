## Example
```shell
docker run -it --rm --tty -w /project -v $(pwd)/:/project  coverage-check check /project/.phpunit.coverage.xml/index.xml
```