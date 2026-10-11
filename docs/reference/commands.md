# Commands

Run commands from the application directory. Load the Cache plugin first.

```bash
bin/cake page_cache status
bin/cake page_cache status '/pages/home?lang=en'
bin/cake page_cache clear '/pages/home?lang=en'
bin/cake page_cache clear
```

Quote URLs containing query strings so the shell does not interpret them.
Commands use global `CacheConfig` values. Controller-only engine, prefix, or key
settings can make their entries inaccessible to these commands; configure shared
settings globally.

`status` lists file-cache statistics or reports the configured engine. With a URL,
it reports that entry's metadata when present. `clear` without a URL clears the
view-cache directory, or calls clear on the configured engine. Use a dedicated
engine configuration if clearing unrelated cache entries would be a problem.

Clear-all asks for confirmation, with `y` as the default. Answer the prompt when
running interactively or supply input explicitly in an automation.
