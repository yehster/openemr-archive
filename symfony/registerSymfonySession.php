<?php

error_log("Symfony Session!");
foreach($_SESSION as $key=>$value)
{
    error_log($key.":".$value);
}
?>
