<?php

require 'autoload.php';

use SlimGenerator\SlimGenerator;

// php slim_generator.php command path_to_slim_project

try {
    (new SlimGenerator($argv))->process();
} catch (Exception $e) {
    exit('❌ Error: ' . $e->getMessage() . PHP_EOL);
}

exit('🚀 Success' . PHP_EOL);