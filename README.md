# w3-validator
Validation Script For git-lab-ci.yml
```yaml
git_test:
  stage: test
  script:
    - export

html_test:
  image: php:7.0
  stage: test
  script:
    - php validate-html/index.php --commit_id=$CI_COMMIT_SHA --project_id=$CI_PROJECT_ID
```

Run the Script using the coment #### php validate-html/index.php --commit_id=$CI_COMMIT_SHA --project_id=$CI_PROJECT_ID
