<?php

class ExceptionHook {
    public function GlobalExceptionHandler() {
        set_exception_handler(array($this, 'GlobalExceptions'));
    }
    public function GlobalExceptions($exception) {
        $msg = 'Exception of type \'' . get_class($exception) . '\' occurred with Message: ' . $exception->getMessage() . ' in File ' . $exception->getFile() . ' at Line ' . $exception->getLine();
        $msg .= $exception->getTraceAsString();
        log_message('error', $msg, TRUE);
        echo $msg; 
        die;// for code debugging purpose
    }
}
