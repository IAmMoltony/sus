<?php
class SusUploadLog
{
    private $log;

    function __construct()
    {
        $this->log = [];
    }

    function message(string $message)
    {
        $this->log[] = "** " . $message;
    }

    function delimiter()
    {
        $this->log[] = "*****************";
    }

    function as_string()
    {
        $log_string = "";
        foreach ($this->log as $message) {
            $log_string .= $message . "\n";
        }
        return $log_string;
    }
}
?>
