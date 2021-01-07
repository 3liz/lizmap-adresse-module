github-pages: 
	@docker run --rm -w /plugin -v $(shell pwd):/plugin 3liz/pymarkdown:latest docs/README.md docs/index.html

eslint:
	npx eslint adresse/

eslint-fix:
	npx eslint --fix adresse/

php-cs-fixer-test:
	php-cs-fixer fix --config=.php_cs.dist --allow-risky=yes --dry-run --diff

php-cs-fixer-apply:
	php-cs-fixer fix --config=.php_cs.dist --allow-risky=yes
