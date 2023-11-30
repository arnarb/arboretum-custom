<?php
/**
 * Pull the powerdash data from their API
 */
function pull_powerdash_data() {
  // Static variables
  $url = 'https://api.powerdash.com/v2/';
  $systems_path = 'systems/';
  $channels_path = 'channeldata/agg/?';
  $username = 'arboretum_api';
  $password = "#M^;GJgu32&\$bM'z";
  $headers = [
    'Authorization: Basic ' . base64_encode($username . ':' . $password)
  ];

  $date = date('Y-m-d\TH:i:s\Z');

  $data = (object) [
    'systems' => array()
  ];

  $systems_url = $url . $systems_path;
  $systems_options = (object) [
    'url'       => $systems_url,
    'headers'   => $headers
  ];

  // cURL Powerdash Locations for systems
  $systems_response = curl_request($systems_options);

  if ($systems_response):
    $systems_decoded = json_decode($systems_response, true);
    $systems = parse_locations($systems_decoded, $date);

    foreach ($systems as $system):
      $system_url = $systems_url . $system->id;
      $system_options = (object) [
        'url'       => $system_url,
        'headers'   => $headers
      ];

      // cURL Powerdash systems for individual channels
      $system_response = curl_request($system_options);

      if ($system_response):
        $system_decoded = json_decode($system_response, true);
        $channels = parse_systems($system_decoded);
        $system->channels = $channels;

        foreach ($channels as $channel):
          $params = (object) [
            'channel_id'  => $channel->id,
            'start_time'  => $system->start_time,
            'end_time'    => $system->end_time,
            'interval'    => $system->interval,
            'total'       => 0
          ];
          $args = http_build_query($params);
          $channel_path = $channels_path . $args;

          $channel_url = $url . $channel_path;
          $channel_options = (object) [
            'url'       => $channel_url,
            'headers'   => $headers
          ];

          // cURL Powerdash individual channels for output values
          $channel_response = curl_request($channel_options);

          if ($channel_response):
            $channel_decoded = json_decode($channel_response, true);

            $channel_val = $channel_decoded['channeldata']['intervals'][count($channel_decoded['channeldata']['intervals']) - 1]['values'][0]['endval'];
            $channel->total = $channel_val;
          endif;
        endforeach;

      endif;
    endforeach;

    // Add output values together and create JSON object of the systems
    foreach ($systems as $system):
      $total = 0;

      foreach ($system->channels as $channel):
        $total += (int)$channel->total;
      endforeach;

      $system_data = (object) [
        'system_name' => substr($system->system_name, 0, strpos($system->system_name, ' PV')),
        'total'       => $total,
        'start_date'  => substr($system->start_time, 0, strpos($system->start_time, 'T'))
      ];

      array_push($data->systems, $system_data);
    endforeach;

    // Output JSON to a file
    $filename = $_SERVER['DOCUMENT_ROOT'] . 'public/solar_data.json';
    require "$filename";
    $file = fopen($filename, 'w');
    fwrite($file, json_encode($data, JSON_PRETTY_PRINT));
    fclose($file);
  endif;
}


/**
 * Create a curl request
 *
 * @param options
 */
function curl_request($options) {
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => $options->url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => $options->headers
  ));

  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  if ($err):
    echo 'ERROR: ', $err;
    return false;
  elseif ($response):
    return $response;
  endif;
}


/**
 * Break the locations apart
 *
 * @param data of the raw values to parse
 * @param date the current date to be stored in the array
 *
 * @return systems array of the systems arrays
 */
function parse_locations($data, $date) {
  $DGH_START = '2016-12-02T00:00:00Z';
  $HUNNEWELL_START = '2017-09-02T00:00:00Z';
  $WELD_START = '2019-12-02T00:00:00Z'; // *
  $WELD = 'Weld';
  $HUNNEWELL = 'Hunnewell';

  $systems = [];

  foreach($data['systems'] as $system):
    if (strpos($system['system_name'], $WELD) !== false && strpos($system['system_name'], $WELD) > -1):
      $start = $WELD_START;
    elseif (strpos($system['system_name'], $HUNNEWELL) !== false && strpos($system['system_name'], $HUNNEWELL) > -1):
      $start = $HUNNEWELL_START;
    else:
      $start = $DGH_START;
    endif;

    $system_obj = (object) [
        'system_name' => $system['system_name'],
        'id' => $system['id'],
        'start_time' => $start,
        'end_time' => $date,
        'interval' => '1day',
        'channels' => null
    ];

    array_push($systems, $system_obj);
  endforeach;

  return $systems;
}


/**
 * Return the channels of the system arrays
 *
 * @param data of the systems
 *
 * @return channels array of the individual array machines
 */
function parse_systems($data) {
  $WELD = 'Weld';
  $TESLA = 'Solar production (Tesla meter)';

  $channels = [];
  foreach ($data['channels'] as $channel):
    if (strpos($data['system_name'], $WELD) === false || strpos($channel['label'], $TESLA) > -1):
      $channel_obj = (object) [
        'channel_name' => $channel['label'],
        'id' => $channel['channel_id'],
        'unit' => $channel['unit_coarse'],
        'type' => $channel['channel_type'],
        'total' => 0
      ];
      array_push($channels, $channel_obj);
    endif;
  endforeach;

  return $channels;
}

pull_powerdash_data();

?>
