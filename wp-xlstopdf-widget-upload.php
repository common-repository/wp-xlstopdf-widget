<?php
set_time_limit(0);
ini_set('default_socket_timeout',480);
echo json_encode(Wpxlstopdfw::process_upload($args));
?>