<?php
  $uri = $_SERVER["REDIRECT_URL"];
  $x = explode("/", $uri);
  $cx = count($x);

  if ($x[$cx - 1] == "sync") {
    $hash = $x[$cx - 2];
    $start = json_decode(file_get_contents("{$hash}/start"));
    $tick = json_decode(file_get_contents("{$hash}/tick"));
    $selected_frag = (int)$tick->fragment - 10;
    $start_tick = $tick->tick - 10 * 128 * 3;

    header("Content-Type: application/json");
    $dt = (time() - $tick->last_receive);
    if ($dt >= 150) {
      $selected_frag = (int)$start->signup_fragment;
      $start_tick = (int)$start->tick;
    }

    echo json_encode([
      "tick" => $start_tick,
      "rtdelay" => $dt,
      "rcvage" => $dt,
      "fragment" => $selected_frag,
      "signup_fragment" => (int)$start->signup_fragment,
      "tps" => (int)$start->tps,
      "protocol" => (int)$start->protocol,
    ]);
    die();
  } 
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>GO Live Player</title>
  <meta name="robots" content="noindex, nofollow">
  <meta name="googlebot" content="noindex, nofollow">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css"/>
</head>
<body>
	<div class="ui container" style="padding-top: 20px">
  <h1>CSGO Live</h1>
  <table class="ui celled table">
    <thead>
      <tr>
        <th>Start Date</th>
        <th>Map</th>
        <th>Tick</th>
        <th>Start at</th>
        <th>Length</th>
        <th>Watch</th>
      </tr>
    </thead>
    <tbody>
<?php
  $files = array_reverse(scandir('.'));
  foreach($files as $hash) {
  if (is_dir($hash) && is_file($hash."/start")) {
    $start = json_decode(file_get_contents("{$hash}/start"));
    $tick = json_decode(file_get_contents("{$hash}/tick"));
    $last_received_at = filemtime("{$hash}/tick");
    $live = time() - $last_received_at < 135;
    $dt = ($start->tick) / $start->tps;
    $dt2 = ($tick->tick) / $start->tps;
    if ($dt2 > 120) {
    ?>
    <tr>
      <td><?=date("Y-m-d H:i:s", filemtime("{$hash}/start"))?></td>
      <td><?=$start->map?></td>
      <td><?=$start->tps?></td>
      <td><?=(int)($dt / 60)?> m <?=(int)($dt % 60)?> s</td>
      <td><?=(int)($dt2 / 60)?> m <?=(int)($dt2 % 60)?> s</td>
      <td>
        <a href="steam://rungame/730/76561202255233023/+playcast%20%22http://<?=$_SERVER['SERVER_NAME']?>/<?=$hash?>" class="ui <?=$live? "red": "green"?> button"><?=$live? "LIVE": "REC"?></a>
      </td>
    </tr>
    <?php
    }
  }
  }
?>
    </tbody>
  </table>
</div>
</body>
</html>