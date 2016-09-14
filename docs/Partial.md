# Partial view caching

## Usage
This uses a component and a view class to partially cache a view.
It expects your layout to contain all the session based or dynamic content which cannot and should not be cached.
The content of the pages, however, can very well be cached, if they do not have any DB driven or session based content.
So if they contain just a lot of links, images, or other more complex but static HTML content, this partial caching concept
can speed things up for you.

The component must be loaded and will automatically switch the View class to the `PartialCacheView` class
if the type of request is "html" and no other component has switched the View class yet.

Please note that this view class expects an `AppView` class to be present in your project, as it needs to have
the same helpers setup etc. Such a class could look like this:
```php
namespace App\View;

use Cake\View\View;

class AppView extends View {

	public function initialize() {
		...
		$this->loadHelper('AssetCompress.AssetCompress');
	}

}
```

### Caching specific controller actions
For each controller you can specify to either cache all actions or explicit ones.

```php
// in your controller

public function initialize() {
	parent::initialize();

	$this->loadComponent('Cache.PartialCache', [
		'actions' => ['index', 'view']
	]);
}
```
By default the duration is unlimited, but you can also set a time.


### Caching some PagesController views
For the PagesController you will need to look for the first "pass" element which represents the page name.

```php
// in PagesController

public function initialize() {
	parent::initialize();

	$pass = current($this->request->params['pass']);
	if ($pass && in_array($pass, ['home', 'some-public-action'])) {
		$this->loadComponent('Cache.PartialCache', [
			...
		]);
	}
}
```
