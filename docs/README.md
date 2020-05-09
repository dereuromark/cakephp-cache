# Cache plugin documentation

## Full-page caching

The simple and fast way. Just cache it "all".
This will not invoke the controller/components even if cache exists.

See [Full-page](Full-page.md) documentation


## Element caching

If you only need a few elements in your view cached.

Use the core build in [element caching](https://book.cakephp.org/4/en/views.html#caching-elements).

## Configuration
Most config keys can be set through global `Configure` key `CacheConfig`.
This should be preferred as you otherwise have to set the same configs for multiple files.
