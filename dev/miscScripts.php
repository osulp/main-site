<?php
/*
* DO NOT RUN THIS AS A WHOLE!
* This is a collection of script snippets that have come in handy for data cleanup
* Some comments exist and extra whitespace is used to logically break up scripts
*/

$nids = \Drupal::entityQuery('node')->condition('type','book')->execute();
$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

// Convert old book pub month & year to date field
foreach ($nodes as $node) {
  // Paper date
  $month = $node->field_paper_month->value;
  $year = $node->field_paper_year->value;

  if ($year) {
    $month = $month ?? '01';

    $node->field_paper_pub_date->value = sprintf("%s-%02d-01", $year, $month);
  }

  // hardcover date
  $month = $node->field_hardcover_month->value;
  $year = $node->field_hardcover_year->value;

  if ($year) {
    $month = $month ?? '01';

    $node->field_hardcover_pub_date->value = sprintf("%s-%02d-01", $year, $month);
  }

  // ebook date
  $month = $node->field_ebook_month->value;
  $year = $node->field_ebook_year->value;

  if ($year) {
    $month = $month ?? '01';

    $node->field_e_book_pub_date->value = sprintf("%s-%02d-01", $year, $month);
  }

  $node->save();
}

// Check if all books have at least one publication date
foreach ($nodes as $node) {
  $flag = true;

  if ($node->field_paper_pub_date->value != null) {
    $flag = false;
  }

  if ($node->field_hardcover_pub_date->value != null) {
    $flag = false;
  }

  if ($node->field_e_book_pub_date->value != null) {
    $flag = false;
  }

  if ($flag) {
    print($node->nid->value);
    print("\n");
  }
}

// Check if all existing ISBNs conform to ISBN format
foreach ($nodes as $node) {
  $val = $node->field_ebook_isbn->value;
  $val = str_replace('-', '', $val);
  $val = str_replace('ISBN', '', $val);
  $val = str_replace(' ', '', $val);
  if ($val && strlen($val) != 13 && strlen($val) != 10) {
    print("ebook:\n");
    print($node->nid->value);
    print("\n");
  }
  $val = $node->field_paper_isbn->value;
  $val = str_replace('-', '', $val);
  $val = str_replace('ISBN', '', $val);
  $val = str_replace(' ', '', $val);
  if ($val && strlen($val) != 13 && strlen($val) != 10) {
    print("paper:\n");
    print($node->nid->value);
    print("\n");
  }
  $val = $node->field_hardcover_isbn->value;
  $val = str_replace('-', '', $val);
  $val = str_replace('ISBN', '', $val);
  $val = str_replace(' ', '', $val);
  if ($val && strlen($val) != 13 && strlen($val) != 10) {
    print("hard:\n");
    print($node->nid->value);
    print("\n");
  }
}

// Convert old book isbn to isbn field
foreach ($nodes as $node) {
  $val = $node->field_ebook_isbn->value;
  $val = str_replace('-', '', $val);
  $val = str_replace('ISBN', '', $val);
  $val = str_replace(' ', '', $val);
  if ($val) {
    $node->field_e_book_isbn->value = $val;
  }
  $val = $node->field_paper_isbn->value;
  $val = str_replace('-', '', $val);
  $val = str_replace('ISBN', '', $val);
  $val = str_replace(' ', '', $val);
  if ($val) {
    $node->field_paper_isbn_new->value = $val;
  }
  $val = $node->field_hardcover_isbn->value;
  $val = str_replace('-', '', $val);
  $val = str_replace('ISBN', '', $val);
  $val = str_replace(' ', '', $val);
  if ($val) {
    $node->field_hardcover_isbn_new->value = $val;
  }
  $node->save();
}

// Check if all books have new isbn where they had old
foreach ($nodes as $node) {
  if ($node->field_ebook_isbn->value && !$node->field_e_book_isbn->value) {
    print("ebook:\n");
    print($node->nid->value);
    print("\n");
  }
  if ($node->field_paper_isbn->value && !$node->field_paper_isbn_new->value) {
    print("paper:\n");
    print($node->nid->value);
    print("\n");
  }
  if ($node->field_hardcover_isbn->value && !$node->field_hardcover_isbn_new->value) {
    print("hard:\n");
    print($node->nid->value);
    print("\n");
  }
}






$nids = \Drupal::entityQuery('node')->condition('type','event')->execute();
$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

foreach ($nodes as $node) {
  $date = new DateTime($node->field_date[0]->value, new DateTimeZone('America/Los_Angeles'));
  $dateEnd = new DateTime($node->field_date[0]->end_value, new DateTimeZone('America/Los_Angeles'));

  $date->setTimeZone(new DateTimeZone('UTC'));
  $dateEnd->setTimeZone(new DateTimeZone('UTC'));

  $node->field_date = [
    'value' => explode('+',$date->format('c'))[0],
    'end_value' => explode('+',$dateEnd->format('c'))[0],
  ];
  echo $node->nid[0]->value;
  echo "\n";
  $node->save();
  unset($date); unset($dateEnd);
}

foreach ($nodes as $node) {
  $date = $node->field_date2[0]->value;

  $node->field_date = [
    'value' => $date,
    'end_value' => $date,
  ];
  $node->save();
}






$nids = \Drupal::entityQuery('node')->condition('type','blog')->execute();
$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

Use PHPHtmlParser\Dom;
$dom = new Dom;
$dom->setOptions([
  'whitespaceTextNode' => false,
  // 'cleanupInput' => false,
  // 'preserveLineBreaks' => true,
]);

/*
 * Recursively find nodes with styles and remove them from the deleted_attrs list
 */
function removeStyles($dom) {
  if ($dom instanceof \PHPHtmlParser\Dom\LeafNode) {
    echo $dom;
    return;
  }
  $deleted_attrs = [
    'font-size',
    'font-family',
    'text-decoration',
    'color',
  ];
  $children = $dom->getChildren();
  foreach ($children as $child) {
    removeStyles($child);
    $attrs = $child->getAttributes();
    $final_attrs = [];
    if (!array_key_exists('style', $attrs)) continue;
    $attrs = array_map('trim', explode(';', $attrs['style']));
    foreach ($attrs as $attr) {
      if (in_array(array_map('trim', explode(':', $attr))[0], $deleted_attrs)) continue;
      $final_attrs[] = $attr;
    }
    $child->setAttribute('style', implode(';', $final_attrs));
  }
}
/*
 * Generate a more compacted summary based on the body value
 */
foreach ($nodes as $node) {
  $node->body[0]->summary = '';
  $dom->load($node->body[0]->value, []);

  // Remove any <br> tags
  $brs = $dom->find('br');
  foreach ($brs as $br) {
    echo $br;
    $br->delete();
  }

  $elems = $dom->find('style');
  foreach ($elems as $elem) {
    $node->body[0]->summary .= $elem->outerHtml . "\n";
  }
  // Find <p> tags and add them back in until we exceed the char count or exhaust <p>'s
  $elems = $dom->find('p');
  $i = 0;
  $len = 0;
  while ($i < count($elems) && $len < 400) {
    $styles = $elems[$i]->find('style');
    if (count($styles)) $styles->delete();
    echo $i;
    removeStyles($elems[$i]);
    $node->body[0]->summary .= $elems[$i]->outerHtml . "\n";
    $len += strlen($elems[$i]->text(true));
    $i++;
  }
  echo "\n" . $node->nid[0]->value . "\n";
  $node->save();
}

// $node = \Drupal\node\Entity\Node::load(9606);$dom->load($node->body[0]->value);

foreach ($nodes as $node) {
  $node->body->summary = null;
  $node->save();
}


$nids = \Drupal::entityQuery('node')->condition('type','series')->execute();
$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
foreach ($nodes as $node) {
  foreach ($node->body as &$field) {
    $field->format = 'rich_text';
  }
  $node->save();
}
