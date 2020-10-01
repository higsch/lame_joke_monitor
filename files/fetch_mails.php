<?php
/*
################################################

Simple PHP script to fetch eMails from gMail
Matthias Stahl, December 2016
Just 4 fun

################################################
*/
// Connect to mailserver, yay you can see our password here
// fill in your credentials
$hostname = 'xxx';
$username = 'xxx';
$password = 'xxx';

// Function from Dennis Wronka to circumvent display problems of smileys etc.
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
	
	// Read latest mail
	$struct = imap_fetchstructure($inbox, $emails[0]);
	$overview = imap_fetch_overview($inbox, $emails[0], 0);
        //$overview = checkBody($overview, $struct);
	$output = imap_fetchbody($inbox, $emails[0], 1);
	$output = checkBody($output, $struct);
	echo("<strong>Yet another nonsense message:</strong><br />".$output."<em>sent by ".str_replace('"', '', $overview[0]->from)."</em><br /><br />Mail your nonsense to <a href='mailto:new.lame.joke@gmail.com'>new.lame.joke@gmail.com</a>.<br /><a target='_blank' href='INSERT A PHP HERE WHERE ALL MESSAGES ARE SHOWN'>Show all messages</a>.");
} 

// Close the connection
imap_close($inbox);

?>
