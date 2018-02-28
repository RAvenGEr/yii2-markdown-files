<?php

namespace dpwlabs\MarkdownPages;

/**
 * NullParser - dummy parser returns un modified contents.
 *
 * @author David Webb
 */
class NullParser extends \cebe\markdown\Parser {
  public function parse($contents) {
    return $contents;
  }
}
