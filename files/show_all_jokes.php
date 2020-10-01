<?php
/*
#################################################################

Simple PHP script to fetch eMails from mailserver and show them all
Matthias Stahl, December 2016
Just 4 fun

#################################################################
*/
// Connect to gmail, yay you can see our password here
$hostname = 'xxx';
$username = 'xxx';
$password = 'xxx';

// Function from Dennis Wronka
function checkBody($body, $struct)
{
    global $mailbox;
    if ($struct->subtype!='PLAIN')
        {
            if ($struct->parts[0]->encoding==3)
                {
                    $body=base64_decode($body);
                }
            if ($struct->parts[0]->encoding==4)
                {
                    $body=quoted_printable_decode($body);
                }
        }
    else
        {
            if ($struct->encoding==3)
                {
                    $body=base64_decode($body);
                }
            if ($struct->encoding==4)
                {
                    $body=quoted_printable_decode($body);
                }
        }
    // MS: corrected for an error when s.o. writes an html mail
    if ($struct->subtype!='ALTERNATIVE')
    {
       $body=nl2br(htmlentities($body));
    }
    else
    {
       $body=nl2br($body);
    }
    if ($struct->subtype=='MIXED')
        {
            $body.="\n";
            for ($part=1;$part<count($struct->parts);$part++)
                {
                    print_r($struct->parts[$part]);
                    if ($struct->parts[$part]->type!=2)
                        {
                            $body.="\t";
                            $body.='<a href="download-attachment.php?mailbox='.$mailbox.'&amp;msgid='.$_GET['show'].'&amp;part='.$part.'">&lt;&lt;'.$struct->parts[$part]->dparameters[0]->value.'&gt;&gt;</a>';
                        }
                }
        }
    return $body;
}

// Try to connect
$inbox = imap_open($hostname, $username, $password) or die('Cannot connect to mailserver: ' . imap_last_error());

// Grab emails
$emails = imap_search($inbox,'ALL');

// If emails are returned, cycle through each...
if($emails) {
	$output = '';
	
	// Put the newest emails on top
	rsort($emails);

	echo("<h2>All nonsense messages</h2><br /><table><tbody><tr><td valign='top' style='padding: 10px 10px'><strong>Date / Time</strong></td><td valign='top' style='padding: 10px 10px'><strong>Sender</strong></td><td valign='top' style='padding: 10px 10px'><strong>Message</strong></td></tr>");

	foreach($emails as $email_number) {
		$struct = imap_fetchstructure($inbox, $email_number);
		$overview = imap_fetch_overview($inbox, $email_number, 0);
		$output = imap_fetchbody($inbox, $email_number, 1);
		$output = checkBody($output, $struct);

		echo("<tr><td valign='top' style='padding: 10px 10px'>".date('d.m.Y / G:i:s', strtotime($overview[0]->date))."</td><td valign='top' style='padding: 10px 10px'><em>".str_replace('"', '', $overview[0]->from)."</em></td><td style='padding: 10px 10px'>".$output."</td></tr>");
//print_r($struct);
	}
	
	echo("</tbody></table>");
} 

// Close the connection
imap_close($inbox);

?>
