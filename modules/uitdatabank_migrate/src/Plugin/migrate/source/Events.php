<?php

namespace Drupal\uitdatabank_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate_plus\Plugin\migrate\source\Url;

/**
 * Source plugin for the Events.
 *
 * @MigrateSource(
 *   id = "events"
 * )
 */
class Events extends Url {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    $params = '?start=0&limit=30&embed=true&apiKey=270cbec1-d89d-43e5-9bac-94ca0757c524';

    if (!is_array($configuration['urls'])) {
      $configuration['urls'] = [$configuration['urls'] . $params];
    }

    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {

    // @todo: make plugin for this.
    // Handle translatable fields, also need to catch language not present
    // source.
    $translatable_fields = [
      'address',
      'description',
      'title',
    ];
    $langcode = $row->getSourceProperty('language');
    $langcode = $langcode?: 'nl';
    foreach ($translatable_fields as $name) {
      $value = $row->getSourceProperty($name);

      if (isset($value[$langcode])) {
        $value = $value[$langcode];
        $row->setSourceProperty($name, $value);
      }
    }
    unset($name, $value, $translatable_fields);

    // Preprocess typicalAgeRange values.
    $value = $row->getSourceProperty('age_min');
    $ages = explode('-', $value);
    foreach ($ages as $index => $age) {
      $ages[$index] = (int) $age;
    }
    $row->setSourceProperty('age_min', isset($ages[0]) ? $ages[0] : 0);
    $row->setSourceProperty('age_max', isset($ages[1]) ? $ages[1] : 0);
    unset($age, $ages, $index, $value);

    return parent::prepareRow($row);
  }

}
