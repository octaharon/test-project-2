<?php

namespace Providers\Shell;

class Result {

    private $output;
    private $status;

    public function __construct($status, $output) {
        $this->output = $output;
        $this->status = $status;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getOutput() {
        return $this->output;
    }

    public function getStringOutput() {
        $result = $this->getOutput();
        if (is_array($result)) {
            $result = implode("\n", $result);
        }

        return $result;
    }
} 