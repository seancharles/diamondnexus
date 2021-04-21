<?php

class DnPublisher {

    private $con;

    public function __construct() {
	$con = ssh2_connect("mag2", 22, array('hostkey'=>'ssh-rsa'));
	if(!($con)) {
	    return false;
	} 
	$auth = ssh2_auth_pubkey_file($con, "webdevlive", 
				'/home/live/.ssh/id_rsa.pub', 
				'/home/live/.ssh/id_rsa', '');
	if(!($auth)) {
	    return false;
	}
	$this->con = $con;
    }

    public function exec_local_command($command) {
	$command_output = system($command);
	if(!($command_output)) {
	    return false;
	}
	return true;
    }

    public function exec_remote_command($command) {
	$stream = ssh2_exec($this->con, $command);
	if(!($stream)) {
	    return false;
	}
	stream_set_blocking($stream, true);
	$data = "";
	while ($buf = fread($stream,4096)) {
	    print $buf;
	}
	fclose($stream);
    }

    public function publishFull() {
	echo "Running Index\n";
	$result = self::exec_local_command("cd ~/html/ ; /usr/bin/php shell/dnIndexer.php full");
	self::buildFeeds();
	return;
    }

    public function publishQuick() {
	echo "Running Index\n";
	$result = self::exec_local_command("cd ~/html/ ; /usr/bin/php shell/dnIndexer.php quick");
	self::buildFeeds();
	return;
    }

    public function publishDropDowns(){
	echo "Running Index\n";
	$result = self::exec_local_command("cd ~/html/ ; /usr/bin/php shell/dnIndexer.php catalog_product_attribute");
	self::buildFeeds();
	return;
    }
    public function buildFeeds() {
	echo "Running Cache fluffer\n";
	$result = self::exec_local_command("cd ~/html/shell/dnl/ ; /usr/bin/php ./cache-warmer.php http://www.diamondnexus.com/sitemap.xml");
	echo "Running Base Feed\n";
	$result = self::exec_remote_command("/usr/bin/php /home/live/html/shell/dnl/google_api/build_feed.php update");
	return;
    }

    public function dnPublish($type='') {
	switch($type) {
            case 'mini':
		self::publishDropDowns();
		break;;
	    case 'quick':
		self::publishDropDowns();
		self::publishQuick();
		break;;
	    case 'feeds':
		self::buildFeeds();
		break;;
	    default:
		self::publishDropDowns();
		self::publishFull();
		break;;
	}
	
    }
}
$type = '';
if(isset($argv[1])) {
    $type = $argv[1];
}
$dnPublish = New DnPublisher();
$dnPublish->dnPublish($type);
?>  
