<?php
if (function_exists('curl_version')) {
    echo "cURL is enabled\n";
    print_r(curl_version());
} else {
    echo "cURL is not enabled\n";
}
?>
