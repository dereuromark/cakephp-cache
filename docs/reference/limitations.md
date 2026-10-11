# Limitations

## Public responses only

Cache keys do not automatically vary by user, session, cookies, request headers,
or authorization. A cache hit skips the controller and later middleware.
Do not cache account pages, admin actions, personalized content, or forms with
session-specific tokens. The Admin prefix alone provides no protection against
cached responses.

The component checks GET requests and configured actions, but does not filter
by response status. Avoid actions that can return redirects or errors for the
same cache key, or use a `when` condition to limit eligible requests.

## Response metadata

The cache preserves the response body, an allowed content-type extension, and
cache timestamps. It does not replay the original status code, cookies, or
custom response headers. Cache hits create a new response with status 200 and
fresh cache headers.

## Storage and invalidation

Updating application data does not automatically invalidate cached pages.
Use short durations or [clear the relevant URL](commands.md) after changes.
Changing a key prefix leaves old entries on disk until cleared.

For engine storage, the engine's own expiry can remove an entry before the
plugin's embedded duration. Configure the engine to retain entries for at least
the longest duration you need.

## Complete bodies

The plugin does not support dynamic sections within a cached body. Use CakePHP
view element caching when only part of a page should be cached.
