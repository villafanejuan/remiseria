<?php
$baseDir = __DIR__;
$files = glob("$baseDir/admin/*.php");
$files = array_merge($files, glob("$baseDir/remisero/*.php"));

foreach ($files as $file) {
    $content = file_get_contents($file);
    $modified = false;
    
    if (strpos($content, '../logout.php') !== false) {
        $content = str_replace('../logout.php', '/remiseria/logout.php', $content);
        $modified = true;
    }
    
    if ($modified) {
        file_put_contents($file, $content);
        echo "Actualizado: $file<br>";
    }
}
echo "Listo!";
