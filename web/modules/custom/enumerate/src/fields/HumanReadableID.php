<?php


namespace Drupal\enumerate\fields;


use Drupal\Core\Field\BaseFieldDefinition;

class HumanReadableID {

  public static function addField(&$fields) {
    $fields['hid'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Machine name'))
      ->setTranslatable(FALSE)
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);
  }
}