<?php

namespace ForeverCompanies\TealiumCron\Controller\Index;

/**
 * PID Create/Delete/Check Class
 *
 * Class for generating, checking, and removing PID files. Used to
 * prevent scripts from running multiple instances of one another.
 */
class ForeverCompanies_Pid
{
    /**
     * path to pid files
     * @access private
     * @var string
     */
    private $_path;

    /**
     * pid file
     * @access private
     * @var string
     */
    private $_file;

    /**
     * is the script running?
     * @access public
     * @var bool
     */
    public $alreadyRunning = false;

    /**
     * ForeverCompanies_Pid constructor.
     * @param string $filename
     * @param string $path
     * @throws Exception
     */
    public function __construct($filename, $path = '')
    {
        // initialize shutdown function
        register_shutdown_function(array($this, 'destruct'));

        // set our path to where the pid file should be written
        if ($path == '') {
            $this->setPath('/tmp/');
        } else {
            $this->setPath($path);
        }

        // set our filename
        $this->setFile($filename);

        // verify we can write the file and/or path
        if (is_writable($this->_file) || is_writable($this->_path)) {
            // check if the pid file exists
            if (file_exists($this->_file)) {
                $pid = (int)trim(file_get_contents($this->_file));
                if (posix_kill($pid, 0)) {
                    $this->alreadyRunning = true;
                }
            }
        } else {
            throw new Exception('PID class cannot write PID file ' . $this->_file);
        }

        // if the script is not already running, create a new pid file with process id
        if (!$this->alreadyRunning) {
            $pid = getmypid();
            file_put_contents($this->_file, $pid);
        }
    }

    /**
     * Destructor - kill process when done
     * @access public
     */
    public function destruct()
    {
        if (!$this->alreadyRunning && file_exists($this->_file) && is_writeable($this->_file)) {
            unlink($this->_file);
        }
    }

    /**
     * Set the name of the pid file being checked, created, removed
     * @access public
     * @param string $filename
     * @throws Exception
     */
    private function setFile($filename)
    {
        // validate our filename is not empty and only contains alphanumeric characters
        if ($filename === '') {
            throw new Exception('PID filename cannot be an empty string');
        }
        if (!ctype_alnum($filename)) {
            throw new Exception('PID filename must contain only alphanumeric characters.');
        }

        // set our file
        $this->_file = $this->_path . $filename . '.pid';
    }

    /**
     * Sets the path
     * @access private
     * @param string $path
     */
    private function setPath($path)
    {
        $this->_path = $path;
    }
}