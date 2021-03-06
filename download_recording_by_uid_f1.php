#!/usr/bin/php
<?php
/**
 * download_recording_by_uid_f1.php
 * 
 * A script which will downloads wav recordings from Opal for a specific UID
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @date 2018-01-29
 */ 

define( 'RECORDING_PATH', '/data/comprehensive/f1' );
define( 'OPAL_HOME_VIEW', 'recordings-home-f1' );
define( 'OPAL_SITE_VIEW', 'recordings-site-all-f1' );
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
$result = send_to_view( OPAL_SITE_VIEW, sprintf( 'valueSet/%s', $uid ) );
if( is_object( $result ) && property_exists( $result, 'valueSets' ) )
{
  foreach( $result->valueSets as $participant )
  {
    $uid = $participant->identifier;
    out( sprintf( 'downloading recordings for "%s"', $uid ) );
    
    // get the site files (already included in the list
    foreach( $participant->values as $recording )
      if( is_object( $recording ) && property_exists( $recording, 'link' ) ) download_file( $recording->link, RECORDING_PATH );

    // get the home files (not included in the list)
    $result = send_to_view( OPAL_HOME_VIEW, sprintf( 'valueSet/%s', $uid ) );
    if( is_object( $result ) && property_exists( $result, 'valueSets' ) )
    {
      $obj = current( $result->valueSets );
      foreach( $obj->values as $recording )
        if( is_object( $recording ) && property_exists( $recording, 'link' ) ) download_file( $recording->link, RECORDING_PATH );
    }
  }
}
else out( 'No data found' );

out( 'done' );
