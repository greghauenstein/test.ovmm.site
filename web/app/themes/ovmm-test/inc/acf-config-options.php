<?php
/*
 * ACF Config Options
 * - DRY configuration options for sharing common settings between ACF
 * - field setups.
 */

// Shared config for the ACF site settings
global $acf_config;

$acf_config = (object) array(
  'wrapper_half'          => [ 'width' => '50' ],
  'wrapper_third'         => [ 'width' => '33' ],
  'wrapper_quarter'       => [ 'width' => '25' ],
  'wrapper_three_quarter' => [ 'width' => '75' ],

  'phone_placeholder'     => '555-555-5555',
  'email_placeholder'     => 'jdoe@the712initiative.org',

	'us_states' => [
		''                          => 'Select a state',
		'Alabama'                   => 'Alabama',
		'Alaska'                    => 'Alaska',
		'Arizona'                   => 'Arizona',
		'Arkansas'                  => 'Arkansas',
		'California'                => 'California',
		'Colorado'                  => 'Colorado',
		'Connecticut'               => 'Connecticut',
		'Delaware'                  => 'Delaware',
		'District of Columbia'      => 'District of Columbia',
		'Florida'                   => 'Florida',
		'Georgia'                   => 'Georgia',
		'Guam'                      => 'Guam',
		'Hawaii'                    => 'Hawaii',
		'Idaho'                     => 'Idaho',
		'Illinois'                  => 'Illinois',
		'Indiana'                   => 'Indiana',
		'Iowa'                      => 'Iowa',
		'Kansas'                    => 'Kansas',
		'Kentucky'                  => 'Kentucky',
		'Louisiana'                 => 'Louisiana',
		'Maine'                     => 'Maine',
		'Maryland'                  => 'Maryland',
		'Massachusetts'             => 'Massachusetts',
		'Michigan'                  => 'Michigan',
		'Minnesota'                 => 'Minnesota',
		'Mississippi'               => 'Mississippi',
		'Missouri'                  => 'Missouri',
		'Montana'                   => 'Montana',
		'Nebraska'                  => 'Nebraska',
		'Nevada'                    => 'Nevada',
		'New Hampshire'             => 'New Hampshire',
		'New Jersey'                => 'New Jersey',
		'New Mexico'                => 'New Mexico',
		'New York'                  => 'New York',
		'North Carolina'            => 'North Carolina',
		'North Dakota'              => 'North Dakota',
		'Northern Marianas Islands' => 'Northern Marianas Islands',
		'Ohio'                      => 'Ohio',
		'Oklahoma'                  => 'Oklahoma',
		'Oregon'                    => 'Oregon',
		'Pennsylvania'              => 'Pennsylvania',
		'Puerto Rico'               => 'Puerto Rico',
		'Rhode Island'              => 'Rhode Island',
		'South Carolina'            => 'South Carolina',
		'South Dakota'              => 'South Dakota',
		'Tennessee'                 => 'Tennessee',
		'Texas'                     => 'Texas',
		'Utah'                      => 'Utah',
		'Vermont'                   => 'Vermont',
		'Virginia'                  => 'Virginia',
		'Virgin Islands'            => 'Virgin Islands',
		'Washington'                => 'Washington',
		'West Virginia'             => 'West Virginia',
		'Wisconsin'                 => 'Wisconsin',
		'Wyoming'                   => 'Wyoming',
	],
);
