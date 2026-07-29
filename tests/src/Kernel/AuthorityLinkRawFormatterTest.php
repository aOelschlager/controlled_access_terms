<?php

namespace Drupal\Tests\controlled_access_terms\Kernel\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests the AuthorityLinkRawFormatter field formatter.
 *
 * @group controlled_access_terms
 */
class AuthorityLinkRawFormatterTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'node',
    'field',
    'link',
    'user',
    'controlled_access_terms',
  ];

  /**
   * The node type for testing.
   *
   * @var \Drupal\node\Entity\NodeType
   */
  protected $nodeType;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('field_storage_config');
    $this->installEntitySchema('field_config');
    $this->installSchema('node', ['node_access']);

    $this->nodeType = NodeType::create([
      'type' => 'test_content',
      'name' => 'Test Content',
    ]);
    $this->nodeType->save();

    $fieldStorage = FieldStorageConfig::create([
      'field_name' => 'field_authority_link',
      'entity_type' => 'node',
      'type' => 'authority_link',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ]);
    $fieldStorage->save();

    FieldConfig::create([
      'field_storage' => $fieldStorage,
      'bundle' => $this->nodeType->id(),
      'label' => 'Authority Link Field',
      'settings' => [
        'authority_sources' => [
          'lcsh' => 'Library of Congress Subject Headings',
          'other' => 'Other',
        ],
      ],
    ])->save();

    \Drupal::service('entity_display.repository')->getViewDisplay('node', $this->nodeType->id(), 'default')
      ->setComponent('field_authority_link', [
        'type' => 'authority_formatter_export',
      ])
      ->save();
  }

  /**
   * Tests that the export formatter exposes source, URI, and title.
   */
  public function testFormatterOutput() {
    $node = Node::create([
      'type' => $this->nodeType->id(),
      'title' => 'Test Authority Link Node',
      'field_authority_link' => [
        [
          'source' => 'lcsh',
          'uri' => 'https://id.loc.gov/authorities/subjects/sh85037549',
          'title' => 'Dogs',
        ],
        [
          'source' => 'unknown',
          'uri' => 'https://example.com/authority/1',
          'title' => '',
        ],
      ],
    ]);
    $node->save();

    $build = $node->get('field_authority_link')->view('default');
    $rendered = \Drupal::service('renderer')->renderInIsolation($build);
    $output = (string) $rendered;

    $this->assertStringContainsString('lcsh|https://id.loc.gov/authorities/subjects/sh85037549|Dogs', $output);
    $this->assertStringContainsString('unknown|https://example.com/authority/1|', $output);
  }

}
