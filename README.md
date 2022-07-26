# CoverageCheck 
A CLI tool that would help to maintan hight level of coverage in a repository with phpuint. 
## Feature
1. Check coverage report with previus value stored in the repository in config file if it worse return error code (1);
2. Modify config value if coverage has imporved and the `--save-improvement` option passed. 

Command check if coverage report better then curren one saved previously in file and commited to repo.
If it worse will return error code. 
If it better and option "--save-improvement" passed then will override value in config with the better one. 
You need to commit the config file change in your CI. `git add coverage-check.xml && git commit -m"Code coverage improved" && git push`

## Example
```shell
docker run -it --rm --tty -w /project -v $(pwd)/:/project  fearofcode/php-coverage-check check /project/.phpunit.coverage.xml/index.xml
```


#### keywords: phpuint coverage tests
