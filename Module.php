<?php

namespace dpwlabs\MarkdownPages;

use yii;
use \yii\helpers\FileHelper;

class Module extends \yii\base\Module {

  public $pages;
  public $drafts;
  public $file_regex = '/([[:digit:]]{4})-([[:digit:]]{2})-([[:digit:]]{2})_([0-9a-z_]*).md$/i';
  public $files;
  public $results;
  public $params = ['recursive' => false, 'only' => ['*.md']];

  public function create($name, $title, $author, $content = "") {
    return save(date('Y-m-d'), $name, $author, $content);
  }

  public function save($date, $name, $title, $author, $content = "") {
    $filename = $date . '_' . $name . '.md';
    if ($this->parseName($filename)) {
      if ($path = $this->getPath("$this->pages/$filename")) {
        FileHelper::createDirectory(dirname($path), 0775, true);
        $fhandle = fopen($path, 'wb');
        if ($fhandle === false)
          throw Exception('Cannot create file: ' . $path);

        $contents = <<<HEREDOC
---------
author: "$author"
title: "$title"
---------

$content
HEREDOC;
        fwrite($fhandle, $contents);
        fclose($fhandle);
        return $path;
      }
    }
    return false;
  }

  public function fetch() {
    $files = FileHelper::findFiles($this->getPath($this->pages), $this->params);
    if (defined('YII_ENV') && (YII_ENV === 'dev' || YII_ENV === 'test')) {
      $files = array_merge($files, FileHelper::findFiles($this->getPath($this->drafts), $this->params));
    }
    $this->files = $files;
    return $this;
  }

  public function parse() {
    $parser = new \Hyn\Frontmatter\Parser(new \cebe\markdown\Markdown);
    $parser->setFrontmatter(\Hyn\Frontmatter\Frontmatters\YamlFrontmatter::class);

    $posts = [];
    foreach ($this->files as $file) {
      $date = $this->parseName($file);
      if ($date) {
        $parsed = $parser->parse(file_get_contents($file));
        array_push($posts, [
            'date' => $date,
            'yaml' => $parsed['meta'],
            'content' => $parsed['html'],
        ]);
      }
    }

    $posts = $this->sort($posts);
    $this->results = $posts;
    return $this;
  }

  public function rawPage($name) {
    return $this->page($name, new NullParser);
  }

  public function page($name, $mdParser = null) {
    $this->fetch($this->params);
    foreach ($this->files as $file) {
      if (Module::endswith($file, $name . '.md')) {
        $date = $this->parseName($file);
        if ($date) {
          if ($mdParser === null) {
            $mdParser = new \cebe\markdown\Markdown;
          }
          $parser = new \Hyn\Frontmatter\Parser($mdParser);
          $parser->setFrontmatter(\Hyn\Frontmatter\Frontmatters\YamlFrontmatter::class);
          $parsed = $parser->parse(file_get_contents($file));
          return ['date' => $date, 'yaml' => $parsed['meta'], 'content' => $parsed['html'],];
        }
      }
    }
  }

  public function savePage($name, $title, $author, $content, $date = null) {
    $this->fetch($this->params);
    if ($date === null) {
      $date = date('Y-m-d');
    }
    foreach ($this->files as $file) {
      if (Module::endswith($file, $name . '.md')) {
        $oldDate = $this->parseName($file);
        if ($oldDate !== $date) {
          unlink($file);
        }
        break;
      }
    }
    $this->save($date, $name, $title, $author, $content);
  }

  public static function endswith($string, $test) {
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen)
      return false;
    return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
  }

  public function parseName($filepath) {
    if (preg_match($this->file_regex, $filepath, $matches)) {
      return [
          'year' => $matches[1],
          'month' => $matches[2],
          'day' => $matches[3],
          'full' => implode("-", array_slice($matches, 1, 3)),
          'name' => $matches[4]
      ];
    }
    return false;
  }

  public function getPath($path) {
    if (!is_string($path))
      throw new \InvalidArgumentException('getPath only accepts a String. $path was: ' . $path);
    return FileHelper::normalizePath(Yii::getAlias($path));
  }

  public function sort($arr) {
    usort($arr, function($a, $b) {
      return $b['date']['full'] <=> $a['date']['full'];
    });
    return $arr;
  }

}
