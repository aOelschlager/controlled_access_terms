<?php

namespace Drupal\controlled_access_terms\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'AuthorityLinkRawFormatter'.
 *
 * Renders the authority source, URI, and title as a single delimited
 * plain-text value, suitable for use in exports (e.g. CSV/Views exports)
 * where the individual parts of an authority link need to remain
 * distinguishable and machine-readable.
 *
 * @FieldFormatter(
 *   id = "authority_formatter_export",
 *   label = @Translation("Authority link export formatter"),
 *   field_types = {
 *     "authority_link"
 *   }
 * )
 */
class AuthorityLinkRawFormatter extends FormatterBase {

  /**
   * The delimiter used to separate source, URI, and title in the output.
   */
  const DELIMITER = '|';

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#plain_text' => $this->buildExportValue($item),
      ];
    }

    return $element;
  }

  /**
   * Builds the delimited export value for a single authority link item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The authority link field item.
   *
   * @return string
   *   A string of the form "source|uri|title", using the raw authority
   *   source key (not its display label).
   */
  protected function buildExportValue(FieldItemInterface $item) {
    return implode(static::DELIMITER, [
      $item->source,
      $item->uri,
      $item->title,
    ]);
  }

}
