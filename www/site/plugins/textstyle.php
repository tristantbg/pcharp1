<?php

/**
 * Text-style Plugin
 *
 * (style: class-name class-name)
 *
 * Text *text* etc.
 *
 * (style)
 *
 */

kirbytext::$pre[] = function($kirbytext, $text) {

  // 1. match text from (style: class-name) to (style)
  $text = preg_replace_callback('!\(style:(.*?)\)(.*?)\(style\)!is', function($matches) use($kirbytext) {

  // 2. select text in string after `: ` and save it in a variable
  $style_class = preg_split('!\:\s(.*?)\)!', trim($matches[1]));

  // 3. select text between parentheses and save it in a variable
  $styled_texts = preg_split('!\(style:(.*?)\)(.*?)\(style\)!', $matches[2]);
  $html         = array();

  // 4. parse text as kirbytext
  foreach($styled_texts as $styled_text) {
    $field  = new Field($kirbytext->field->page, null, trim($styled_text));
    $html[] = kirbytext($field);
  }

  // 5. return text
  return '<div class="' . implode($style_class) . '">' . implode($html) . '</div>';

  }, $text);

  return $text;

};