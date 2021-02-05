<?php

date_default_timezone_set('America/Los_Angeles');
require_once('/scripts/fmochales/class.phpmailer.php');

echo "Script start.\n";

$date = date('Y-m-d', time());

$pbx = 'adamsfinancialgro';

$command_purge = 'ssh -q -o ConnectTimeout=5 -i /var/www/id_dsa_ipbx ipbx@'.$pbx.'.vo.packet8.net \'find ~ipbx/VM_CDR_Repository/'.$pbx.'/voiceapps/DEAD -type f -name "*.au*" -mtime +31 -print0 | xargs -0 rm \'';
$exec = my_shell_exec($command_purge);

$command = 'ssh -q -o ConnectTimeout=5 -i /var/www/id_dsa_ipbx ipbx@'.$pbx.'.vo.packet8.net \'find ~ipbx/VM_CDR_Repository/'.$pbx.'/voiceapps/users/*/mailbox -type f -name "*.au*" -mtime +15 \' >> /scripts/fmochales/Purged/logs/'.$date.'_voicemailPurge.log';

$exec = my_shell_exec($command);

$command = 'ssh -q -o ConnectTimeout=5 -i /var/www/id_dsa_ipbx ipbx@'.$pbx.'.vo.packet8.net \'mkdir ~ipbx/VM_CDR_Repository/'.$pbx.'/voiceapps/DEAD | find ~ipbx/VM_CDR_Repository/'.$pbx.'/voiceapps/users/*/mailbox -type f -name "*.au*" -mtime +15 -print0 | xargs -0 mv -t ~ipbx/VM_CDR_Repository/'.$pbx.'/voiceapps/DEAD\' >> /scripts/fmochales/Purged/logs/'.$date.'_VoicemailPurge.log';

$exec = my_shell_exec($command);

sendEmail($date);

function my_shell_exec($command) {

	$descriptorspec = array(
	   0 => array("pipe", "r"),  // stdin
	   1 => array("pipe", "w"),  // stdout
	   2 => array("pipe", "w"),  // stderr
	);

	$process = proc_open($command, $descriptorspec, $pipes, dirname(__FILE__), null);

	$stdout = stream_get_contents($pipes[1]);
	fclose($pipes[1]);
	$stderr = stream_get_contents($pipes[2]);
	fclose($pipes[2]);

	$return_code = proc_close( $process );

	return array('stdout' => $stdout, 'stderr' => $stderr, 'return_code' => $return_code);

}

function sendEmail($date)
{
	$email = new PHPMailer();
	$email->From      = 'DoNotReply@8x8.com';
	$email->FromName  = '8x8 PS-IC';
	$email->Subject   = $date.' adamsfinancialgro Voicemail deletion log';
	$email->Body      = 'adamsfinancialgro voicemail deletion log attached.';
	$email->AddAttachment('/scripts/fmochales/Purged/logs/'.$date.'_voicemailPurge.log');
	$email->AddAddress('fmochales@8x8.com');
    $email->AddAddress('jfranic@8x8.com');
	$email->AddAddress('pakrap@8x8.com');
	$email->AddAddress('ndragicevic@8x8.com');
	$email->AddAddress('PS-Americas-IC-alerts@8x8.com');
	$email->Send();
}

?>

