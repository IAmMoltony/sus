<?php
class SusConfig
{
    private $config;

    function __construct()
    {
        $this->config = json_decode(file_get_contents("config.json"));
        $this->set_default_values();
    }

    private function set_default_values()
    {
        if (!isset($this->config->root_save_path)) {
            $this->config->root_save_path = "./files";
        }
    }

    function get_root_save_path()
    {
        return $this->config->root_save_path;
    }

    function is_blacklisted($mime_type)
    {
        if ($this->has_blacklist()) {
            return in_array($mime_type, $this->config->mime_blacklist);
        }

        if ($this->has_whitelist()) {
            return !in_array($mime_type, $this->config->mime_whitelist);
        }

        return false;
    }

    private function has_blacklist()
    {
        return isset($this->config->mime_blacklist);
    }

    private function has_whitelist()
    {
        return isset($this->config->mime_whitelist);
    }
}
?>
