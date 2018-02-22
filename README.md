# Yii2 Markdown Pages

yii2-markdown-pages provides a simple way to write pages in individual Markdown files with YAML frontmatter, render them on the fly and use the rendered HTML and frontmatter however you like.

Give updates on your Yii2 site or have a list of posts for a simple blog or news feed. Store your posts in version control with the rest of your code. **No database required!**

This extension was forked from CorWatts/yii2-markdown-files

## Installation
Install via composer:  
```bash
composer require 'dpwlabs/yii2-markdown-pages'
```

## Configuration
Enable the module by adding the snippet below to your main.php configuration file. 

```php
'modules' => [
  'pages' => [ // name this module what you like
    'class' => \dpwlabs\MarkdownPages\Module::className(),
    'posts' => '@frontend/views/markdown/pages',
    'drafts' => '@frontend/views/markdown/drafts',
  ]
],
```
- `class`: is the namespaced class for this module  
- `pages`: is a path pointing to the directory containing publishable markdown files. The path can contain Yii2 aliases.  
- `drafts`: is a path pointing to the directory containing markdown files that aren't quite ready for publishing. The path can contain Yii2 aliases. **Drafts are only rendered in the Yii2 `dev` environment.**

**Note:** If you're going to use the included console command ensure this configuration is added somewhere the console application can access (like `common/config/main.php`).

## Usage
Before rendering and displaying posts the individual post files must be created. A simple way to scaffold new posts is using the console command included in this extension. See below for instructions on how to set it up and use it.

It is easy to create new posts _without_ the included console command. Posts follow a specific ruleset:  

- Create a file in the `pages` or `drafts` directory path specified in the module configuration above.  
-  Similar to Jekyll, the filename has a specific format. It should start with the date (YYYY-MM-DD format) followed by a snake_cased description, and ending with the `.md` extension. Something like `2017-05-20_test_post_1.md`. When these files are processed the date is extracted from the filename. The rest of the descriptive filename is used to select when using the `page()` method.
