<?php
// https://stackoverflow.com/a/22885011
class ExecutionTime
{
    private $start_time;
    private $end_time;

    public function start()
    {
        $this->start_time = getrusage();
    }

    public function end()
    {
        $this->end_time = getrusage();
    }

    public function get_runtime()
    {
        // omits syscall time, only userspcae
        return ($this->end_time["ru_utime.tv_sec"] * 1000 + intval($this->end_time["ru_utime.tv_usec"] / 1000)) - ($this->start_time["ru_utime.tv_sec"] * 1000 + intval($this->start_time["ru_utime.tv_usec"] / 1000));
    }

    public function __toString()
    {
        $runtime = $this->get_runtime();
        $runtime_s = $runtime / 1000;
        return "$runtime ms ($runtime_s s)";
    }
}
?>
