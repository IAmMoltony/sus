<?php
class SusUploadLog
{
    private $log;

    public function __construct()
    {
        $this->log = [];
    }

    public function message(string $message)
    {
        $this->log[] = "** " . $message;
        error_log("sus/uploadlog -> $message");
    }

    public function delimiter()
    {
        $this->log[] = "*****************";
    }

    public function __toString()
    {
        $log_string = "";
        foreach ($this->log as $message) {
            $log_string .= $message . "\n";
        }
        return $log_string;
    }
}
?>
