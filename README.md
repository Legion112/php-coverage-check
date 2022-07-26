# CoverageCheck 
A CLI tool that would help to maintan hight level of coverage in a repository with phpuint. 
## Feature
1. Check coverage report with previus value stored in the repository in config `coverage-check.xml` file if it worse return error code (1);
2. Modify config value if coverage has imporved and the `--save-improvement` option passed. 
<img width="1099" alt="image" src="https://user-images.githubusercontent.com/20613430/180978624-e3cb1850-618f-4d1c-acb5-862ddc7af7d9.png">

Command check if coverage report better then curren one saved previously in file and commited to repo.
If it worse will return error code. 
If it better and option "--save-improvement" passed then will override value in config with the better one. 
You need to commit the config file change in your CI. `git add coverage-check.xml && git commit -m"Code coverage improved" && git push`

## Example
```shell
docker run -it --rm --tty -w /project -v $(pwd)/:/project  fearofcode/php-coverage-check check /project/.phpunit.coverage.xml/index.xml
```


#### keywords: phpuint coverage tests
