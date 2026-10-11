import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'cakephp-cache',
  description: 'Full-page response caching for public CakePHP controller actions.',
  base: '/cakephp-cache/',
  cleanUrls: true,
  srcExclude: ['README.md', 'Full-page.md'],
  lastUpdated: true,
  head: [
    ['link', { rel: 'icon', href: '/cakephp-cache/favicon.svg', type: 'image/svg+xml' }],
  ],
  sitemap: { hostname: 'https://dereuromark.github.io/cakephp-cache/' },
  themeConfig: {
    logo: '/logo.svg',
    nav: [
      { text: 'Installation', link: '/guide/installation' },
      { text: 'Getting started', link: '/guide/getting-started' },
      { text: 'Reference', link: '/reference/configuration' },
    ],
    sidebar: [
      {
        text: 'Guide',
        items: [
          { text: 'Overview', link: '/guide/' },
          { text: 'Installation', link: '/guide/installation' },
          { text: 'Getting started', link: '/guide/getting-started' },
          { text: 'Contributing', link: '/guide/contributing' },
        ],
      },
      {
        text: 'Reference',
        items: [
          { text: 'Configuration', link: '/reference/configuration' },
          { text: 'Commands', link: '/reference/commands' },
          { text: 'Limitations', link: '/reference/limitations' },
        ],
      },
    ],
    search: { provider: 'local' },
    socialLinks: [{ icon: 'github', link: 'https://github.com/dereuromark/cakephp-cache' }],
    editLink: {
      pattern: 'https://github.com/dereuromark/cakephp-cache/edit/cake6/docs/:path',
      text: 'Edit this page on GitHub',
    },
    footer: { message: 'Released under the MIT License.' },
  },
})
