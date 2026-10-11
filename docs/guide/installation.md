# Installation

This development branch requires CakePHP 6 and PHP 8.5. Run these commands
from your application directory:

```bash
composer config minimum-stability dev
composer config prefer-stable true
composer require cakephp/cakephp:^6.0@dev dereuromark/cakephp-cache:dev-cake6
bin/cake plugin load Cache
```

The plugin provides commands but does not automatically add its middleware.
Follow [Getting started](getting-started.md) to connect the reader and writer.
There are no plugin database migrations.
