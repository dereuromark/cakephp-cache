# Contributing

Clone the repository, install dependencies, and run its checks:

```bash
composer install
composer test
composer cs-check
composer stan-setup
composer stan
```

Build the documentation with:

```bash
cd docs
npm ci
npm run docs:build
```

Use `npm run docs:dev` to preview edits. Check examples against the component,
middleware, and commands before changing documented behavior.
