<?php
session_start();
session_destroy();
header("Location: /remiseria/");
exit;
