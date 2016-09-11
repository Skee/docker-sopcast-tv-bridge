<?php

$supervisor_conf_template = <<<EOF
[program:NAME]
command=/app/sp-auth/sp-sc-auth URL SPORT EPORT
autorestart=true

EOF;

$supervisor_confd_path = "/etc/supervisor/conf.d";

$mgmt_port_idx = 34000;
$stream_port_idx = 35000;

$my_ip = `ip a l dev eth0 | grep inet | head -n 1 | awk '{print $2}' | awk -F/ '{print $1}'`;
$my_ip = trim($my_ip);

$stations_json = file_get_contents("/app/streams.json", "r");
$stations = json_decode($stations_json);

$fd_playlist = fopen("/var/www/html/playlist.m3u", "w");
fwrite($fd_playlist, "#EXTM3U\n");


$counter = 0;
foreach($stations->groups as $group) {
	foreach($group->channels as $channel) {
		if(($channel->language == "ro" || $channel->language == "en") && 0 === strpos($channel->address, "sop://")) {
			$counter++;
			echo "$channel->name [$channel->language] -- $channel->address\n";
			$mgmt_port = $mgmt_port_idx + $counter;
			$stream_port = $stream_port_idx + $counter;
			$clean_name = preg_replace('/[^a-zA-Z0-9-_\.]/','', $channel->name);
			$tpl_search = array("NAME", "URL", "SPORT", "EPORT");
			$tpl_replace = array($counter.'-'.$clean_name, escapeshellarg($channel->address), $mgmt_port, $stream_port);
			$supervisor_worker_config = str_replace($tpl_search, $tpl_replace, $supervisor_conf_template);
			$fd_supervisor_config = fopen($supervisor_confd_path . '/' . $counter . '-' . $clean_name . '.conf', 'w');
			fwrite($fd_supervisor_config, $supervisor_worker_config);
			fclose($fd_supervisor_config);
			fwrite($fd_playlist, "#EXTINF:$counter tvg-id=\"$channel->name\" group-title=\"$group->name\",$channel->name\n");
			fwrite($fd_playlist, "http://$my_ip:$stream_port/tv.mp4\n");
		}
	}
}
fclose($fd_playlist);



