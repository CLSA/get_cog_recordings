#!/usr/bin/php
<?php
/**
 * download_home_recording_only.php
 * 
 * A script which will download HOME wav recordings from Opal.
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @date 2018-10-30
 */ 

define( 'RECORDING_PATH', '/data/comprehensive/f1' );
define( 'OPAL_HOME_VIEW', 'recordings-home-f1' );
define( 'SCRIPT_PATH', '/home/patrick/files/scripts/get_cog_recordings' );
include SCRIPT_PATH.'/common.php';

if( 2 == $argc )
{
  $uid = $argv[1];
}
else
{
  error( 'You must provide a UID (identifier) to download' );
  return 1;
}

out( 'getting list of recordings for '.$uid );
$result = send_to_view( OPAL_HOME_VIEW, sprintf( 'valueSet/%s', $uid ) );
if( is_object( $result ) && property_exists( $result, 'valueSets' ) )
{
  $value_set = current( $result->valueSets );
  $uid = $value_set->identifier;

  out( sprintf( 'downloading recordings for "%s"', $uid ) );

  foreach( $value_set->values as $recording )
    if( is_object( $recording ) && property_exists( $recording, 'link' ) ) download_file( $recording->link, RECORDING_PATH );
}
else out( 'No data found' );

out( 'done' );
