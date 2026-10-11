# Overview

The Cache component saves selected controller response bodies. The Cache
middleware reads them on later GET requests and returns a response without
running the controller or middleware placed after it.

Use this for public output that is identical for every caller of the same URL.
For individual view elements, use [CakePHP element caching](https://book.cakephp.org/5/en/views.html#caching-elements).

Start with [Installation](installation.md), then [Getting started](getting-started.md).
See [Limitations](../reference/limitations.md) before enabling caching for an action.
