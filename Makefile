# One front door for the everyday commands.

.PHONY: up down logs test test-image

up: ## Build and start the production image on http://localhost:8080
	docker compose up -d --build

down: ## Stop it
	docker compose down

logs: ## Follow its logs
	docker compose logs -f

test: ## Run the test suite on this machine
	php artisan test

test-image: ## Run the test suite inside the image, exactly as CI does
	docker build --target test .
