<?php

namespace hypeJunction\Places;

use ElggObject;

/**
 * Place class
 *
 * @package    hypeJunction
 * @subpackage Places
 */
class Place extends ElggObject {

	const SUBTYPE = 'hjplace';
	const CHECKIN_DURATION = 60; // 1 HOUR

	/**
	 * Initialize object attributes
	 * @return void
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
	}

	/**
	 * Get an array of address components
	 * @return array
	 */
	public function getAddress() {
		return array(
			'street_address' => $this->street_address,
			'extended_address' => $this->extended_address,
			'locality' => $this->locality,
			'region' => $this->region,
			'postal_code' => $this->postal_code,
			'country_code' => $this->country_code,
			'country' => $this->country,
		);
	}

	/**
	 * Get the duration of a checkin
	 * @return integer Duration in seconds
	 */
	public function getCheckinDuration() {
		$minutes = elgg_get_plugin_setting('checkin_duration', PLUGIN_ID);
		if (!$minutes) {
			$minutes = self::CHECKIN_DURATION;
		}
		return $minutes * 60;
	}

	/**
	 * Get checkins
	 *
	 * @param array   $options  Options to pass to elgg_get_annotations
	 * @param boolean $all_time Restrict to currently checked in users
	 * @return array|false
	 */
	public function getCheckins($options = array(), $all_time = false) {
		$defaults = array(
			'guids' => $this->guid,
			'annotation_names' => 'checkin',
			'annotation_created_time_lower' => time() - $this->getCheckinDuration(),
		);
		$options = array_merge($defaults, $options);
		if ($all_time) {
			unset($options['annotation_create_time_lower']);
		}

		return elgg_get_annotations($options);
	}

	/**
	 * Check if the user is checked in at the place
	 *
	 * @param integer $user_guid GUID of the user to check
	 * @return integer Count of checkins within checkin duration limit
	 */
	public function isCheckedIn($user_guid = 0) {

		if (!$user_guid) {
			$user_guid = elgg_get_logged_in_user_guid();
		}

		return elgg_get_annotations(array(
			'guids' => $this->guid,
			'annotation_owner_guids' => sanitize_int($user_guid),
			'annotation_names' => 'checkin',
			'annotation_created_time_lower' => time() - $this->getCheckinDuration(),
			'count' => true,
		));
	}

	/**
	 * Checkin a user to this place
	 *
	 * @param integer $user_guid GUID of the user to checking
	 * @return integer ID of the checkin annotation
	 */
	public function checkIn($user_guid = 0) {
		if (!$user_guid) {
			$user_guid = elgg_get_logged_in_user_guid();
		}
		if (!$user_guid) {
			return false;
		}

		$id = $this->annotate('checkin', $user_guid, $this->access_id, $user_guid, 'integer');

		return $id;
	}

}
