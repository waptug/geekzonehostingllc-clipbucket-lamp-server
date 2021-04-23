<?php
class errorhandler extends ClipBucket
{
	public $error_list = array();
	public $message_list = array();
	public $warning_list = array();

    /**
     * @param null $message
     * @param bool $secure
     */
	private function add_error($message=NULL,$secure=true) {
	    $this->error_list[] = array('val' => $message, 'secure' => $secure);
	}

    /**
     * @return array
     */
	public function get_error()
    {
        return $this->error_list;
    }

    public function flush_error() {
        $this->error_list = array();
    }

    /**
     * @param null $message
     * @param bool $secure
     */
	private function add_warning($message=NULL,$secure=true) {
		$this->warning_list[] = array('val' => $message, 'secure' => $secure);
	}

    public function get_warning()
    {
        return $this->warning_list;
    }

    public function flush_warning() {
        $this->warning_list = array();
    }

    /**
     * Function used to add message_list
     *
     * @param null $message
     * @param bool $secure
     */
	public function add_message($message=NULL,$secure=true) {
	    $this->message_list[] = array('val' => $message, 'secure' => $secure);
	}

    public function get_message()
    {
        return $this->message_list;
    }

	public function flush_msg() {
		$this->message_list = array();
	}

	public function flush() {
		$this->flush_msg();
		$this->flush_error();
		$this->flush_warning();
	}

    /**
     * Function for throwing errors that users can see
     *
     * @param  : { string } { $message } { error message to throw }
     * @param string $type
     * @param bool   $secure
     *
     * @return array : { array } { $this->error_list } { an array of all currently logged errors }
     */
	function e($message = NULL, $type ='e', $secure = true) {
		switch($type)
        {
			case 'm':
			case 1:
			case 'msg':
			case 'message':
                $this->add_message($message, $secure);
                break;

			case 'e':
			case 'err':
			case 'error':
				$this->add_error($message, $secure);
			    break;

			case 'w':
			case 2:
			case 'war':
			case 'warning':
			default:
				$this->add_warning($message, $secure);
			    break;
		}
		return $this->error_list;
	}

}
