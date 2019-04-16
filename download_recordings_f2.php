#!/usr/bin/php
<?php
/**
 * A script which will get a list of all participants who have site recordings.
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @date 2018-12-11
 */

define( 'RECORDING_PATH', '/data/comprehensive/f2' );
define( 'OPAL_CHECK_VIEW', 'recordings-check-f2' );
define( 'OPAL_HOME_VIEW', 'recordings-home-f2' );
define( 'OPAL_SITE_VIEW', 'recordings-site-all-f2' );
define( 'CHUNK_SIZE', 1000 );
define( 'MAX_OFFSET', 60000 );
define( 'SCRIPT_PATH', '/home/patrick/files/scripts/get_cog_recordings' );
include SCRIPT_PATH.'/common.php';

out( 'checking for new recordings' );

for( $offset = 0; $offset < MAX_OFFSET; $offset += CHUNK_SIZE )
{
  // get the check data for this offset
  $result = send_to_view( OPAL_CHECK_VIEW, sprintf( 'valueSets?offset=%d&limit=%d', $offset, CHUNK_SIZE ) );

  // stop processing if there is no more data left (means we have passed the maximum)
  if( !is_object( $result ) || !property_exists( $result, 'valueSets' ) ) break;

  foreach( $result->valueSets as $obj )
  {
    $uid = $obj->identifier;
    $var = current( $obj->values );
    $check = 'true' == $var->value;

    if( $check && !file_exists( sprintf( '%s/%s', RECORDING_PATH, $uid ) ) )
    {
      out( sprintf( 'Downloading recordings for %s', $uid ) );

      $sub_result = send_to_view( OPAL_SITE_VIEW, sprintf( 'valueSet/%s', $uid ) );
      if( is_object( $sub_result ) && property_exists( $sub_result, 'valueSets' ) )
      {
        foreach( $sub_result->valueSets as $participant )
        {
          // get the site files (already included in the list
          foreach( $participant->values as $recording )
            if( is_object( $recording ) && property_exists( $recording, 'link' ) ) download_file( $recording->link, RECORDING_PATH );

          // get the home files (not included in the list)
          $sub_result2 = send_to_view( OPAL_HOME_VIEW, sprintf( 'valueSet/%s', $uid ) );
          if( is_object( $sub_result2 ) && property_exists( $sub_result2, 'valueSets' ) )
          {
            $obj = current( $sub_result2->valueSets );
            foreach( $obj->values as $recording )
              if( is_object( $recording ) && property_exists( $recording, 'link' ) ) download_file( $recording->link, RECORDING_PATH );
          }
        }
      }
      else out( 'No data found' );
    }
  }
}

out( 'done' );
