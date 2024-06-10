<?php

namespace Drupal\osu_user_to_profiles\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Drupal 7 Migrate First Name field.
 *
 * @MigrateSource(
 *   id = "d7_user_profile_osu_employee",
 *   source_module = "osu_profiles"
 * )
 */
class UserProfileOsuEmployee extends DrupalSqlBase {

  /**
   * {@inheritDoc}
   */
  public function getIds() {
    return [
      'uid' => [
        'type' => 'integer',
        'alias' => 'p',
      ],
      'pid' => [
        'type' => 'integer',
        'alias' => 'p',
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function query() {
    $query = $this->select('profile', 'p');
    $query->fields('p', ['pid', 'uid']);
    $query->leftJoin('field_data_affiliated_with', 'fdaw', 'fdaw.entity_id = p.pid');
    $query->leftJoin('field_data_department_preferred_name', 'fddpn', 'fddpn.entity_id = fdaw.affiliated_with_target_id');
    $query->leftJoin('field_data_building_and_room', 'fdbar', 'fdbar.entity_id = p.pid');
    $query->leftJoin('field_data_room_number', 'fdrn', 'fdrn.entity_id = fdbar.building_and_room_value');
    $query->innerJoin('field_data_office_location', 'fdol', 'fdol.entity_id = fdbar.building_and_room_value');
    $query->leftJoin('field_data_building_long_name', 'fdbln', 'fdbln.entity_id = fdol.office_location_target_id');
    $query->leftJoin('field_data_building_location', 'fdbl', 'fdbl.entity_id = fdol.office_location_target_id');
    $query->leftJoin('field_data_location_address_one', 'fdlao', 'fdlao.entity_id = fdbl.building_location_target_id');
    $query->leftJoin('field_data_location_address_two', 'fdlat', 'fdlat.entity_id = fdbl.building_location_target_id');
    $query->leftJoin('field_data_location_city', 'fdlac', 'fdlac.entity_id = fdbl.building_location_target_id');
    $query->leftJoin('field_data_location_state', 'fdlas', 'fdlas.entity_id = fdbl.building_location_target_id');
    $query->leftJoin('field_data_location_zip', 'fdlaz', 'fdlaz.entity_id = fdbl.building_location_target_id');
    $query->addField('fddpn', 'department_preferred_name_value', 'department');
    $query->addField('fdrn', 'room_number_value', 'room');
    $query->addField('fdbln', 'building_long_name_value', 'building_name');
    $query->addField('fdlao', 'location_address_one_value');
    $query->addField('fdlat', 'location_address_two_value');
    $query->addField('fdlac', 'location_city_value');
    $query->addField('fdlas', 'location_state_value');
    $query->addField('fdlaz', 'location_zip_value');
    $query->condition('p.type', 'osu_employee');
    $query->distinct();
    return $query;
  }

  /**
   * {@inheritDoc}
   */
  public function fields() {
    return [
      'pid' => $this->t('The Profile ID'),
      'uid' => $this->t('The User ID'),
      'department' => $this->t('The Org the user belongs to'),
      'building_name' => $this->t('The building name value'),
      'room' => $this->t('The room number'),
      'location_address_one_value' => $this->t('The First line of the address'),
      'location_address_two_value' => $this->t('The Second line of the address'),
      'location_city_value' => $this->t('The City'),
      'location_state_value' => $this->t('The State'),
      'location_zip_value' => $this->t('The Postal Zip Code'),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function prepareRow(Row $row) {
    $room_number = $row->get('room');
    $building_name = $row->get('building_name');
    $address_line_1 = $row->get('location_address_one_value');
    $address_line_2 = $row->get('location_address_two_value');
    // If we have a room number set and no address value two is set in the source
    // combine building name and room number and set it to address one
    // moving address one to address two.
    if (!empty($room_number) && empty($address_line_2)) {
      $building_room_with_name = $building_name . ' ' . $room_number;
      $row->setSourceProperty('location_address_two_value', $address_line_1);
      $row->setSourceProperty('location_address_one_value', $building_room_with_name);
    }
    return $row;
  }

}
