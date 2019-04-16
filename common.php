<?php
/**
 * Common defines and functions used by other recording scripts
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @date 2018-12-11
 */ 

require_once( 'settings.ini.php' );

/**
 * Prints a message with the date and time
 * @param string $message The message to output
 */
function out( $message )
{
  printf( "[%s] %s\n", date( 'Y-m-d H:i:s' ), $message );
}

/**
 * Prints an error message
 * @param string $message The error message
 */
function error( $message )
{
  out( sprintf( "ERROR: %s\n", $message ) );
}

/**
 * Sends an http request to opal using the system's curl program
 * @param string $url
 * @param array $arguments An associative array of arguments to pass to curl
 * @param array $headers An associative array of headers to pass to curl
 * @return The json-decoded result of the request
 */
function send( $url, $arguments = array(), $headers = array() )
{
  $url = sprintf( 'https://%s:%d/ws%s', OPAL_URL, OPAL_PORT, $url );
  $headers['Accept'] = 'application/json';
  $headers['Content-Type'] = 'application/json';
  $headers['Authorization'] =
    sprintf( 'X-Opal-Auth %s', base64_encode( sprintf( '%s:%s', OPAL_USERNAME, OPAL_PASSWORD ) ) );

  $arguments['silent'] = '';
  $arguments['insecure'] = '';
  $command = sprintf( 'curl "%s"', $url );
  foreach( $headers as $name => $value )
    $command .= sprintf( ' --header "%s: %s"', $name, $value );
  foreach( $arguments as $name => $value )
    $command .= sprintf( ' --%s%s', $name, 0 < strlen( $value ) ? sprintf( ' "%s"', $value ) : '' );

  $output = '';
  $return_var = NULL;
  exec( $command, $output, $return_var );
  if( 0 != $return_var )
  {
    error( sprintf( "unable to read from opal\n  command: \"%s\"\n  returned: \"%s\"",
                    $command,
                    $return_var ) );
  }
  return 0 < count( $output ) ? json_decode( $output[0] ) : NULL;
}

/**
 * Sends an http request to the home or site view in Opal
 * @param string $type Either "home" or "site" (which view to reference
 * @param string $path A path to add after the view's base path
 * @param array $arguments An associative array of arguments to pass to curl
 * @param array $headers An associative array of headers to pass to curl
 * @return The json-decoded result of the request
 */
function send_to_view( $table, $path = '', $arguments = array(), $headers = array() )
{
  return send( sprintf(
      '/datasource/%s/view/%s%s',
      OPAL_DATASOURCE,
      $table,
      0 < strlen( $path ) ? '/'.$path : ''
    ),
    $arguments,
    $headers
  );
}

/**
 * Downloads a recording.  The file will be named based on its path.
 * @param string $opal_path The full opal-service path to the recording
 * @param string $recording_path The path to where the file should be downloaded
 */
function download_file( $opal_path, $recording_path )
{
  $uid = NULL;
  $variable = NULL;
  $path = str_replace( '/entity', '', $opal_path );
  $last_part = NULL;
  foreach( explode( '/', $path ) as $part )
  {
    if( 'valueSet' == $last_part ) $uid = $part;
    else if( 'variable' == $last_part ) $variable = $part;
    $last_part = $part;
  }

  // make sure the directory exists
  $dir = sprintf( '%s/%s', $recording_path, $uid );
  if( !is_dir( $dir ) ) mkdir( $dir );

  // and write the recording
  $filename = sprintf( '%s/%s.wav', $dir, $variable );
  send( $path, array( 'output' => $filename ) );
}
