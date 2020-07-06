<?php

App::uses('CakeLog', 'Log');

class RcauthSourceUtils
{

  /**
   * HttpCurlClient
   *
   * @param $url      The URL used to address the request
   * @param $fields   List of query parameters in a key=>value array format
   * @param $error
   * @param $info
   * @param array $options
   * @return bool|string
   * @throws Exception
   */
  public static function HttpCurlClient($url, $fields, &$error, &$info, $options = NULL)
  {
    //url-ify the data for the POST
    $fields_string = "";
    foreach ($fields as $key => $value) {
      $fields_string .= $key . '=' . $value . '&';
    }
    rtrim($fields_string, '&');
    // open connection
    $ch = curl_init();

    // set the url, number of POST vars, POST data
    // Content-type: application/x-www-form-urlencoded => is the default approach for post requests
    if(empty($options) || isset($options['curlType']) == 'POST'){
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, count($fields));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    }
    else{ //GET Request
      curl_setopt($ch, CURLOPT_POST, FALSE);
      curl_setopt($ch, CURLOPT_URL, $url.'?'.$fields_string);
    }
    curl_setopt($ch, CURLOPT_HEADER, !empty($options) && isset($options['header']) ? $options['header'] : FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, !empty($options) && isset($options['returnTransfer']) ? $options['returnTransfer'] : TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, !empty($options) && isset($options['followLocation']) ? $options['followLocation'] : FALSE);
    curl_setopt($ch, CURLOPT_VERBOSE, !empty($options) && isset($options['verbose']) ? $options['verbose'] : TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, !empty($options) && isset($options['timeout']) ? $options['timeout'] : 3000);

    // execute post
    $response = curl_exec($ch);
    
    // fixme: Make curl throw an dnot return the errors
    $error = "";
    if (empty($response)) {
      // probably connection error
      $error = curl_error($ch);
      if (Configure::read('debug')) {
        CakeLog::write('error', __METHOD__ . ':: Http Request Failed::' . $error);
      }
    }

    $info = curl_getinfo($ch);

    // close connection
    curl_close($ch);
    // return success
    return $response;
  }
}

?>