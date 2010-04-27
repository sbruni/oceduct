<?php
/**
 * SMTP Class
 *
 * 	**** EXAMPLES ****
 * 	Example Usage:
 *
 * 	To send the same email to multiple users
 * 	$smtp = new SMTP;
 * 	$smtp->strHost = 'yourhost';
 * 	$smtp->strSubject = $subject;
 * 	$smtp->strBody = $body;
 * 	$smtp->EmailFrom('user1@domain.com', 'User Name');
 * 	$smtp->EmailTo(array('user2@domain.com', 'user3@domain.com'), array('User Name', 'Second Name'));
 * 	$smtp->SendToAll();
 *
 * 	To send, to one or more users, DIFFERENT e-mails created dynamicly
 * 	$smtp = new SMTP;
 * 	$smtp->strHost = 'yourhost';
 * 	$smtp->EmailFrom('user1@domain.com');
 * 	while ($yourlistofemails) {
 * 		$smtp->EmailTo($dynamic_email, $dynamic_name);
 * 		$smtp->strSubject = $dynamic_subject;
 * 		$smtp->strBody = $dynamic_body;
 * 		$smtp->SendToOne();
 * 	}
 * 	$smtp->Stop();

	To attach one or more files:
	(must be before $smtp->SendTo*)
	$smtp->AttachFile('yourfile', $optional NEW filename, $optional FILE Type);
	**** EXAMPLES ****



	**** SMTP Reply codes (taken from RFC 2821) ****
	211 System status, or system help reply
	214 Help message
		(Information on how to use the receiver or the meaning of a
		particular non-standard command; this reply is useful only
		to the human user)
	220 <domain> Service ready
	221 <domain> Service closing transmission channel
	250 Requested mail action okay, completed
	251 User not local; will forward to <forward-path>
	252 Cannot VRFY user, but will accept message and attempt
		delivery

	354 Start mail input; end with <CRLF>.<CRLF>

	421 <domain> Service not available, closing transmission channel
	(This may be a reply to any command if the service knows it
	must shut down)
	450 Requested mail action not taken: mailbox unavailable
	(e.g., mailbox busy)
	451 Requested action aborted: local error in processing
	452 Requested action not taken: insufficient system storage
	500 Syntax error, command unrecognized
	(This may include errors such as command line too long)
	501 Syntax error in parameters or arguments
	502 Command not implemented (see section 4.2.4)
	503 Bad sequence of commands
	504 Command parameter not implemented
	550 Requested action not taken: mailbox unavailable
	(e.g., mailbox not found, no access, or command rejected
	for policy reasons)
	551 User not local; please try <forward-path>
	(See section 3.4)
	552 Requested mail action aborted: exceeded storage allocation
	553 Requested action not taken: mailbox name not allowed
	(e.g., mailbox syntax incorrect)
	554 Transaction failed  (Or, in the case of a connection-opening
	response, "No SMTP service here")

	**** RFC INFO ****
	This script conforms to RFCs:
	(where 2 rfcs are listed (ie 821/2821) the later is used)
	821/2821, 822/2822, 2045, 2046, 2047, 2048, 2049, 2183

	RFC 821/2821: Simple Mail Transfer Protocol
	RFC 822/2822: Internet Message Format
	RFC 2045:  MIME Part One: Format of Internet Message Bodies
	RFC 2046:  MIME Part Two: Media Types
	RFC 2047:  MIME Part Three: Message Header Extensions for Non-ASCII Text
	RFC 2048:  MIME Part Four: Registration Procedures
	RFC 2049:  MIME  MIME Part Five: Conformance Criteria and Examples
	RFC 2183:  Defines the syntax and sematics of the "Content-Disposition"
		header to convey presentational information.
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package smtp
 */
class Smtp
{
	/**
	 * SMTP requires \r\n not \n
	 *
	 * @var string
	 * @since version 1.0.0
	 */
	const CRLF								= "\r\n";

	/**
	 * Line Feed, default line ending \n
	 *
	 * @var string
	 * @since version 1.0.0
	 */
	const LF								= "\n";

	/**
	 * Amount of byes in a single line
	 * used when retriving data
	 *
	 * @var integer
	 * @since version 1.0.0
	 */
	const LINE								= 1024;

	/**
	 * 211 System status, or system help reply
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const SYSTEM_STATUS						= 211;

	/**
	 * 214 Help message
	 * (Information on how to use the receiver or the meaning of a particular
	 * non-standard command; this reply is useful only to the human user)
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const HELP_MESSAGE						= 214;

	/**
	 * 220 <domain> Service ready
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const SERVICE_READY						= 220;

	/**
	 * 221 <domain> Service closing transmission channel
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const SERVICE_CLOSING					= 221;

	/**
	 * 250 Requested mail action okay, completed
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const OK								= 250;

	/**
	 * 251 User not local; will forward to <forward-path>
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const USER_NOT_LOCAL_WILL_FORWARD		= 251;

	/**
	 * 252 Cannot VRFY user, but will accept message and attempt delivery
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const CANT_VRFY_USER					= 252;

	/**
	 * 354 Start mail input; end with <CRLF>.<CRLF>
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const START_MAIL_INPUT					= 354;

	/**
	 * 421 <domain> Service not available, closing transmission channel
	 * (This may be a reply to any command if the service knows it must shutdown)
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const SERVICE_NOT_AVAILABLE				= 421;

	/**
	 * 450 Requested mail action not taken: mailbox unavailable
	 * (e.g., mailbox busy)
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const MAILBOX_UNAVAILABLE_BUSY			= 450;

	/**
	 * 451 Requested action aborted: local error in processing
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const LOCAL_ERROR						= 451;

	/**
	 * 452 Requested action not taken: insufficient system storage
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const INSUFFICIENT_SYSTEM_STORAGE		= 452;

	/**
	 * 500 Syntax error, command unrecognized
	 * (This may include errors such as command line too long)
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const SE_COMMAND_UNRECOGNIZED			= 500;

	/**
	 * 501 Syntax error in parameters or arguments
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const SE_PARAMETERS_ARGUMENTS			= 501;

	/**
	 * 502 Command not implemented
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const COMMAND_NOT_IMPLEMENTED			= 502;

	/**
	 * 503 Bad sequence of commands
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const BAD_SEQUENCE						= 503;

	/**
	 * 504 Command parameter not implemented
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const COMMAND_PARMS_NOT_IMPLEMENTED		= 504;

	/**
	 * 550 Requested action not taken: mailbox unavailable
	 * (e.g., mailbox not found, no access, or command rejected for policy reasons)
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const MAILBOX_UNAVAILABLE				= 550;

	/**
	 * 551 User not local; please try <forward-path>
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const USER_NOT_LOCAL_NO_FORWARD			= 551;

	/**
	 * 552 Requested mail action aborted: exceeded storage allocation
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const EXCEEDED_STORAGE					= 552;

	/**
	 * 553 Requested action not taken: mailbox name not allowed
	 * (e.g., mailbox syntax incorrect)
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const MAILBOX_NAME_NOT_ALLOWED			= 553;

	/**
	 * 554 Transaction failed  (Or, in the case of a connection-opening
	 * response, "No SMTP service here")
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const TRANSACTION_FAILED				= 554;

	/**
	 * Whenever there is an error, the code will return false instead of
	 * throwing an exception, this way you can simply ignore it or recover
	 * from it in whatever way you want
	 *
	 * SOME exceptions WILL STILL be thrown
	 *
	 * @var bool
	 * @since version 1.0.0
	 */
	public $errorsRecoverable;

	/**
	 * Attaches an attachment to be sent by the NEXT mail() or queueMail()
	 *
	 * $file MUST be the FULL path to the file
	 *
	 * An optional $filename can be given, if so this overrides the real files
	 * name.
	 *
	 * $contentType is the type that the attachment will be listed as
	 *
	 * @param string $file
	 * @param string $filename [optional]
	 * @param string $contentType [optional]
	 * @since version 1.0.0
	 */
	public function addAttachment($file, $filename = '',
		$contentType = 'application/octet-stream')
	{
		if (!file_exists($file))
		{
			if ($this->errorsRecoverable === true)
			{
				$this->_errors[] = 'File '.$file.' does not exist';
				return false;
			}
			throw new SmtpException('File '.$file.' does not exist');
		}

		if (empty($filename))
		{
			$filename = basename($file);
		}

		if (empty($contentType))
		{
			$contentType = 'application/octet-stream';
		}

		$this->_attachments[] = array(
			'filename' => $filename,
			'full' => $file,
			'contentType' => $contentType
		);
	}

	/**
	 * Clears all current info i.e subject body attachments
	 *
	 * @since version 1.0.0
	 */
	public function clear()
	{
		unset($this->_attachments);
		unset($this->_body);
		unset($this->_bodyType);
		unset($this->_subject);

		$this->_attachments = array();
		$this->_subject = '';
		$this->_body = '';
		$this->_bodyType = 'text/plain; charset=US-ASCII';
	}

	/**
	 * Clears all currently attached attachments
	 *
	 * @since version 1.0.0
	 */
	public function clearAttachments()
	{
		unset($this->_attachments);
		$this->_attachments = array();
	}

	/**
	 * Disconnect from SMTP server (NOT required)
	 *
	 * You don't have to disconnect from SMTP
	 * on class destruction the connection is closed properly
	 *
	 * @since version 1.0.0
	 */
	public function disconnect()
	{
		$this->_disconnect();
	}

	/**
	 * Get all current errors
	 *
	 * @return array
	 * @since version 1.0.0
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

	/**
	 * Gets an array of all the commands sent and replies that were preformed
	 * during this run.
	 *
	 * @return array
	 * @since version 1.0.0
	 */
	public function getRawCommandsAndReplies()
	{
		return $this->_rawCR;
	}

	/**
	 * Sends an email (opens and DOES NOT close connection)
	 *
	 * If you want to manully disconnect call it
	 *
	 * REQUIRES ONE OF THE FOLLOWING 3
	 * TO
	 * CC
	 * BCC
	 * if all 3 are empty then we error out
	 * but if ANY of the 3 are here we use them
	 * each is sent with it's own RCPT TO command
	 *
	 * $bccMethod MUST be 1 - 4 (defaults to 3)
	 * From RFC 2821 and 2822 there are 3 ways BCC headers SHOULD be sent
	 * 1. Remove it completely but send to each address
	 * 2. Show all BCC items to ONLY those in the BCC listing
	 * 3 (2a). Remove all other BCC address except for the person reciving it
	 * 4 (3). Show an empty BCC to all recipients indicating that BCC was sent to someone
	 *
	 * @param string $from
	 * @param mixed $to [optional]
	 * @param array $cc [optional]
	 * @param string $replyto [optional]
	 * @param array $bcc [optional]
	 * @param integer $bccMethod [optional]
	 * @return bool
	 * @since version 1.0.0
	 */
	public function mail($from, $to = '', array $cc = array(), $replyto = '',
		array $bcc = array(), $bccMethod = 3)
	{
		// verify required data
		if (empty($to) && empty($cc) && empty($bcc))
		{
			if ($this->errorsRecoverable === true)
			{
				$this->_errors[] = 'One of the following 3 MUST exist: '.
				'TO CC BCC';
			}
			throw new SmtpException('One of the following 3 MUST exist: '.
				'TO CC BCC', SmtpException::INVALID_PARAM);
		}
		if (empty($from))
		{
			throw new SmtpException('$from', SmtpException::PARAM_EMPTY);
		}

		// make a single address to the correct array
		if (!empty($to) && !is_array($to))
		{
			$to = array($to);
		}

		// verify from address
		if (!$this->validateAddressFormat($from))
		{
			throw new SmtpException('From: '.$from, SmtpException::INVALID_ADDRESS);
		}

		// verify replyto address
		if (!empty($replyto) && !$this->validateAddressFormat($replyto))
		{
			throw new SmtpException('ReplyTo: '.$replyto, SmtpException::INVALID_ADDRESS);
		}

		// connect if not already connected
		if ($this->_checkConnection() === false)
		{
			return false;
		}

		// from
		if ($this->_command('MAIL FROM', array($this->_extractAddress($from))) === false)
		{
			return false;
		}

		// send ALL TO CC AND BCC as seperate rcpt to commands
		// first merge them all into one array
		$allAddresses = array();
		if (!empty($to))
		{
			$allAddresses = array_merge($allAddresses, $to);
		}
		if (!empty($cc))
		{
			$allAddresses = array_merge($allAddresses, $cc);
		}
		if (!empty($bcc))
		{
			$allAddresses = array_merge($allAddresses, $bcc);
		}

		// don't send multiple times to the same person
		$allAddresses = array_unique($allAddresses);

		// headers subject body etc...
		// put them together before looping through all the addresses
		// that way we don't have to generate it each time
		$message = array();

		// start sending headers
		$message[] = 'From: '.$from;

		if (!empty($replyto))
		{
			$message[] = 'Reply-To: '.$replyto;
		}

		if (!empty($to))
		{
			$message[] = 'To: '.implode(' ', $to);
		}

		// CC
		if (!empty($cc))
		{
			$message[] = 'CC: '.implode(' ', $cc);
		}

		$message[] = 'bcc';

		// RFC 2822 date format
		$message[] = 'Date: '.date('r');
		$message[] = 'Subject: '.$this->_subject;

		if (empty($this->_attachments))
		{
			$message[] = 'Content-Type: '.$this->_bodyType;
			$message[] = 'Content-Transfer-Encoding: 7BIT';
			$message[] = self::LF.$this->_body;
		}
		else
		// THERE ARE ATTACHMENTS
		{
			$boundary = '=_abcd'.rand(1000, 9999).'xyz';

			// default headers
			$message[] = 'MIME-version: 1.0';
			$message[] = 'Content-Type: multipart/mixed; boundary="'.$boundary.'"';
			$message[] = 'Content-Transfer-Encoding: 7BIT';

			// add the body
			$body = '--'.$boundary.self::LF;
			$body .= 'Content-Type: '.$this->_bodyType.self::LF;
			$body .= 'Content-Transfer-Encoding: 7BIT'.self::LF;
			$body .= 'Content-Description: Mail message body'.self::LF.self::LF;
			// required the above 2 self::LF
			$body .= $this->_body.self::LF;

			foreach ($this->_attachments as $file)
			{
				if (is_readable($file['full']))
				{
					$body .= self::LF.'--'.$boundary.self::LF;
					$body .= 'Content-Type: '.$file['contentType'].self::LF;
					$body .= 'Content-Transfer-Encoding: BASE64'.self::LF;
					$body .= 'Content-Disposition: attachment; filename="'.
						$file['filename'].'"'.self::LF.self::LF;
					// required the above 2 self::LF
					$body .= chunk_split(base64_encode(file_get_contents($file['full']))).self::LF;
				}
			}
			$body .= '--'.$boundary.'--';
			$message[] = $body;
		}

		// close the body by sending a CRLF.CRLF
		// (the ending CRLF is done with in the method _writeLine)
		// THIS MUST BE THE END OF $message array
		$message[] = self::CRLF.'.';

		// go through all the addresses sending them one by one
		foreach ($allAddresses as $address)
		{
			if (!$this->validateAddressFormat($address))
			{
				if ($this->errorsRecoverable === true)
				{
					$this->_errors[] = 'Invalid E-Mail address: To OR Cc OR Bcc: '.
						$address;
					return false;
				}
				throw new SmtpException('To OR Cc OR Bcc: '.$add,
					SmtpException::INVALID_ADDRESS);
			}

			if ($this->_command('RCPT TO', array($this->_extractAddress($address))) === false)
			{
				return false;
			}

			// start message sending
			if ($this->_command('DATA') === false)
			{
				return false;
			}

			// send all above headers
			foreach ($message as $msg)
			{
				if ($msg == 'bcc' && !empty($bcc))
				{
					/**
					 * From RFC 2821 and 2822 there are 3 ways BCC headers SHOULD
					 * be sent
					 * 1. Remove it completely but send to each address
					 * 2. Show all BCC items to ONLY those in the BCC listing
					 * 3 (2a). Remove all other BCC address except for the person
					 * reciving it
					 * 4 (3). Show an empty BCC to all recipients indicating that BCC
					 * was sent to someone
					 *
					 * Defaults to method 3
					 */

					switch ($bccMethod)
					{
						case 1:
							break;
						case 2:
							if (in_array($address, $bcc))
							{
								$this->_writeLine('BCC: '.implode(' ', $bcc));
							}
							break;
						case 4:
							$this->_writeLine('BCC: ');
							break;
						case 3:
						default:
							// defaults to method 3
							if (in_array($address, $bcc))
							{
								$this->_writeLine('BCC: '.$address);
							}
					}
				}
				elseif ($msg != 'bcc')
				{
					$this->_writeLine($msg);
				}
			} // end messages

			// get the reply to the closing of the DATA
			$reply = $this->_getLine();
			if ($reply['code'] != self::OK)
			{
				if ($this->errorsRecoverable === true)
				{
					$this->_errors[] = $reply['reply'];
					return false;
				}
				throw new SmtpException($reply['reply'], $reply['code']);
			}
		} // end all address

		return true;
	}

	/**
	 * Resets any current actions
	 * useful to run before starting a new email
	 *
	 * @return bool
	 * @since version 1.0.0
	 */
	public function reset()
	{
		// start the connection say ehlo etc..
		if ($this->_checkConnection() === false)
		{
			return false;
		}

		return $this->_command('rset');
	}

	/**
	 * Body of the message
	 *
	 * $contentType defaults to text/plain; charset=US-ASCII
	 *
	 * @param string $body
	 * @param string $contentType [optional]
	 * @since version 1.0.0
	 */
	public function setBody($body, $contentType = '')
	{
		$this->_body = replaceNewLines($body, self::LF);

		if (empty($contentType))
		{
			$this->_bodyType = 'text/plain; charset=US-ASCII';
		}
		else
		{
			$this->_bodyType = $contentType;
		}
	}

	/**
	 * Sets the subject to send, CAN be empty
	 *
	 * @param string $subject
	 * @since version 1.0.0
	 */
	public function setSubject($subject)
	{
		// strip all newlines from the subject
		$this->_subject = replaceNewLines($subject, '');
	}

	/**
	 * Verifies that the given e-mail address is in the correct format
	 * Address SHOULD be given in a FULL email format i.e.
	 * John Doe <johndoe@mydomain.com> (make sure to include the < >)
	 * Addresses MAY use full quoted strings and contain comments
	 *
	 * code updated/modified: by Steven Bruni (mainly changed it to my coding style)
	 * written by:            Clay Loveless <clay@killersoft.com>
	 * code taken from:       http://www.killersoft.com/contrib/
	 * @param string $emailAddress
	 * @return bool
	 * @since version 1.0.0
	 */
	public function validateAddressFormat($emailAddress)
	{
		// Some shortcuts for avoiding backslashes
		// NOTE: these MUST be within SINGLE QUOTES
		$esc			= '\\\\';
		$period			= '\.';
		$space			= '\040';
		$tab			= '\t';
		$openBr			= '\[';
		$closeBr		= '\]';
		$openParen		= '\(';
		$closeParen		= '\)';
		$nonAscii		= '\x80-\xff';
		$ctrl			= '\000-\037';
		// note: this should really be only \015
		$crList			= '\n\015';
		// END NOTE

		// Items 19, 20, 21
		// see table on page 295 of 'Mastering Regular Expressions'
		// for within "..."
		$qText = '[^'.$esc.$nonAscii.$crList.'"]';
		// for within [...]
		$dText = '[^'.$esc.$nonAscii.$crList.$openBr.$closeBr.']';
		// an escaped character
		$quotedPair = ' '.$esc.' [^'.$nonAscii.'] ';

		// Items 22 and 23, comment.
		// Impossible to do properly with a regex,
		// I make do by allowing at most
		// one level of nesting.
		$cText = ' [^'.$esc.$nonAscii.$crList.'()] ';

		// $cNested matches one non-nested comment.
		// It is unrolled, with normal of $cText, special of $quotedPair.
		// ( normal* (special normal*)* )
		$cNested = $openParen.$cText.'*(?: '.
			$quotedPair.' '.$cText.'* )*'.$closeParen;

		// $comment allows one level of nested parentheses
		// It is unrolled, with normal of $cText, special of ($quotedPair|$cNested)
		// ( normal* ( special normal* )* )
		$comment = $openParen.$cText.'*(?:(?: '.$quotedPair.' | '.$cNested.
			' )'.$cText.'*)*'.$closeParen;

		// $x is optional whitespace/comments
		// Nab whitespace
		$x = '['.$space.$tab.']*(?: '.$comment.' ['.$space.$tab.']* )*';

		// Item 10: atom
		$atomChar = '[^('.$space.')<>\@,;:".'.$esc.$openBr.$closeBr.
			$ctrl.$nonAscii.']';

		// some number of atom characters ...
		// ... not followed by something that could be part of an atom
		$atom = $atomChar.'+'.'(?!'.$atomChar.')';

		// Item 11: doublequoted string, unrolled.
		// " normal ( special normal* )*
		$quotedStr = '"'.$qText.' *(?: '.$quotedPair.' '.$qText.' * )*"';

		// Item 7: word is an atom or quoted string
		// Atom or Quoted string
		$word = '(?:'.$atom.'|'.$quotedStr.')';

		// Item 12: domain-ref is just an atom
		$domainRef = $atom;

		// Item 13: domain-literal is like a quoted string, but [...]
		// instead of "..."
		$domainLit = $openBr.'(?: '.$dText.' | '.$quotedPair.' )*'.$closeBr;

		// Item 9: sub-domain is a domain-ref or a domain-literal
		$subDomain = '(?:'.$domainRef.'|'.$domainLit.')'.$x;

		// Item 6: domain is a list of subdomains separated by dots
		$domain = $subDomain.'(?:'.$period.' '.$x.' '.$subDomain.')*';

		// Item 8: a route. A bunch of "@ $domain" separated by commas,
		// followed by a colon.
		$route = '\@ '.$x.' '.$domain."(?: , $x \@ $x $domain )*:".$x;

		// Item 5: local-part is a bunch of $word separated by periods
		$localPart = $word.' '.$x.'(?:'.$period.' '.$x.' '.$word.' '.$x.')*';

		// Item 2: addr-spec is local@domain
		$addrSpec = $localPart.' \@ '.$x.' '.$domain;

		// Item 4: route-addr is <route? addr-spec>
		// optional route
		$routeAddr = '< '.$x.'(?: '.$route.' )?'.$addrSpec.'>';

		// Item 3: phrase........
		// like ctrl, but without tab
		$phraseCtrl = '\000-\010\012-\037';

		// Like atom-char, but without listing space, and uses phrase_ctrl.
		// Since the class is negated, this matches the same as atom-char
		// plus space and tab
		$phraseChar = '[^()<>\@,;:".'.$esc.$openBr.$closeBr.$nonAscii.
			$phraseCtrl.']';

		// We've worked it so that $word, $comment, and $quotedStr to not
		// consume trailing $x because we take care of it manually.
		// leading word "normal" atoms and/or spaces "special" comment or
		// quoted string more "normal"
		$phrase = $word.$phraseChar.' *(?:(?: '.$comment.' | '.$quotedStr.
			' )'.$phraseChar.' *)*';

		// Item 1: mailbox is an addr_spec or a phrase/route_addr
		// optional leading comment -- address -- or -- name and address
		$mailbox = $x.'(?:'.$addrSpec.'|'.$phrase.'  '.$routeAddr.')';

		// check it and return
		$check = preg_match('/^'.$mailbox.'$/xS', $emailAddress);
		if ($check === 0 || $check === false)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Class constructor
	 *
	 * @param string host [optional]
	 * @param integer $port [optional]
	 * @since version 1.0.0
	 */
	public function __construct($host = 'localhost', $port = 25, $hostFrom = '',
		$timeout = 30)
	{
		// host
		if (empty($host) || !is_string($host))
		{
			$host = 'localhost';
		}
		$this->_host = $host;

		// port
		if (empty($port) || !is_numeric($port))
		{
			$port = 25;
		}
		$this->_port = $port;

		if (empty($hostFrom) && !empty($_SERVER['HTTP_HOST']))
		{
			$this->_hostFrom = $_SERVER['HTTP_HOST'];
		}
		elseif (!empty($hostFrom))
		{
			$this->_hostFrom = $hostFrom;
		}

		if (!empty($timeout))
		{
			$this->_timeout = $timeout;
		}

		// ini
		$this->_attachments = array();
		$this->_subject = '';
		$this->_body = '';
		$this->_bodyType = 'text/plain; charset=US-ASCII';
		$this->errorsRecoverable = false;
	}

	/**
	 * Class Deconstructor
	 *
	 * @since version 1.0.0
	 */
	public function __destruct()
	{
		$this->_disconnect();
	}

	/**
	 * Connection resource
	 * @var resource
	 * @since version 1.0.0
	 */
	private $_connection;

	/**
	 * SMTP host
	 * @var integer
	 * @since version 1.0.0
	 */
	private $_host;

	/**
	 * SMTP Port
	 * @var integer
	 * @since version 1.0.0
	 */
	private $_port;

	/**
	 * The calling host (defaults to $_SERVER['HTTP_HOST'])
	 *
	 * @var string
	 * @since version 1.0.0
	 */
	private $_hostFrom;

	/**
	 * Timeout of connection (default 30 seconds)
	 *
	 * @var integer
	 * @since version 1.0.0
	 */
	private $_timeout;

	/**
	 * All attached attachments
	 *
	 * @var array
	 * @since version 1.0.0
	 */
	private $_attachments;

	/**
	 * Recoverable errors that were received
	 *
	 * @var array
	 * @since version 1.0.0
	 */
	private $_errors;

	/**
	 * Raw commands and replies that were sent and received
	 *
	 * @var array
	 * @since version 1.0.0
	 */
	private $_rawCR;

	/**
	 * Subject defaults to empty
	 *
	 * @var string
	 * @since version 1.0.0
	 */
	private $_subject;

	/**
	 * Body defaults to empty
	 *
	 * @var string
	 * @since version 1.0.0
	 */
	private $_body;

	/**
	 * Body Content-Type
	 * Default: text/plain; charset=US-ASCII
	 *
	 * @var string
	 * @since version 1.0.0
	 */
	private $_bodyType;

	/**
	 * Checks the current connection if there is one nothing happens if there
	 * is no active connection it'll try to connect
	 *
	 * @return bool
	 * @since version 1.0.0
	 */
	private function _checkConnection()
	{
		if (is_resource($this->_connection) === false)
		{
			// no active connection try to reconnect
			if ($this->_connect() === false)
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Cleans a connection of any unused data
	 * flush connection after request
	 * cleans up unused data
	 *
	 * @since version 1.0.0
	 */
	private function _clearConnection()
	{
		// disable blocking
		if (stream_set_blocking($this->_connection, false) === false)
		{
			throw new SmtpException('Could not disable blocking',
				SmtpException::CONNECTION_ERROR);
		}

		// get all remaining data
		do
		{
			$data = trim(fgets($this->_connection, self::LINE));
		}
		while (strlen($data) > 0);

		// enable blocking
		if (stream_set_blocking($this->_connection, true) === false)
		{
			throw new SmtpException('Could not enable blocking',
				SmtpException::CONNECTION_ERROR);
		}
	}

	/**
	 * Runs a command against the SMTP server
	 *
	 * @param string $command
	 * @param array $args [optional]
	 * @return bool
	 * @since version 1.0.0
	 */
	private function _command($command, array $args = array())
	{
		switch (strtolower($command))
		{
			case 'data':
				$this->_writeLine('DATA');
				$reply = $this->_getLine();
				if ($reply['code'] == self::START_MAIL_INPUT)
				{
					return true;
				}
				break;
			case 'ehello':
			case 'ehlo':
				// send EHLO
				$this->_writeLine('EHLO '.$this->_hostFrom);
				$reply = $this->_getLine('', true);
				if ($reply['code'] == self::OK)
				{
					return true;
				}
				elseif ($reply['code'] == self::COMMAND_NOT_IMPLEMENTED)
				{
					// send HELO instead
					return $this->_command('HELO');
				}
				break;
			case 'hello':
			case 'helo':
				// send HELO
				$this->_writeLine('HELO '.$this->_hostFrom);
				$reply = $this->_getLine('', true);
				if ($reply['code'] == self::OK)
				{
					return true;
				}
				break;
			case 'mail from':
				// MAIL FROM
				$this->_writeLine('MAIL FROM:<'.$args[0].'>');
				$reply = $this->_getLine();
				if ($reply['code'] == self::OK)
				{
					return true;
				}
				break;
			case 'noop':
				// NOOP
				$this->_writeLine('NOOP');
				$reply = $this->_getLine(self::OK);
				if ($reply['code'] == self::OK)
				{
					return true;
				}
				break;
			case 'quit':
				// quit
				$this->_writeLine('QUIT');
				$reply = $this->_getLine();
				if ($reply['code'] == self::SERVICE_CLOSING)
				{
					// was closed successfully return
					return true;
				}
				break;
			case 'rcpt to':
				$this->_writeLine('RCPT TO:<'.$args[0].'>');
				$reply = $this->_getLine();
				if ($reply['code'] == self::OK)
				{
					return true;
				}
				break;
			case 'reset':
			case 'rset':
				$this->_writeLine('RSET');
				$reply = $this->_getLine();
				if ($reply['code'] == self::OK)
				{
					return true;
				}
				break;
			default:
				// if no recognized command is given
				return false;
		}

		switch ($reply['code'])
		{
			case self::SERVICE_NOT_AVAILABLE:
				// log the recoveable error
				$this->_errors[] = $reply;
				return false;
			default:
				if ($this->errorsRecoverable === true)
				{
					$this->_errors[] = $reply;
					return false;
				}
				throw new SmtpException(': '.self::CRLF.$reply['reply'],
					$reply['code']);
		}

		return false;
	}

	/**
	 * Connects to SMTP server and verifies the user
	 *
	 * @return bool
	 * @since version 1.0.0
	 */
	private function _connect()
	{
		// open the connection
		$errno = '';
		$strerr = '';

		$this->_connection = fsockopen($this->_host, $this->_port, $errno,
			$strerr, $this->_timeout);

		// make sure connection is active
		if ($this->_connection === false ||
			is_resource($this->_connection) === false)
		{
			throw new SmtpException('Error('.$errno.') '.$strerr.
				'. Could not connect to host:"'.$this->_host.
				'" on port:"'.$this->_port.'".', SmtpException::CONNECTION_FAILED);
		}

		// set stream settings
		if (stream_set_timeout($this->_connection, $this->_timeout) === false)
		{
			throw new SmtpException('Could not set stream timeout',
				SmtpException::CONNECTION_ERROR);
		}
		if (stream_set_blocking($this->_connection, true) === false)
		{
			throw new SmtpException('Could not enable blocking',
				SmtpException::CONNECTION_ERROR);
		}

		$reply = $this->_getLine();
		if ($reply['code'] != self::SERVICE_READY)
		{
			if ($this->errorsRecoverable === true)
			{
				$this->_errors[] = $reply;
				return false;
			}
			// invalid response
			throw new SmtpException(': '.self::CRLF.$reply['reply'],
				$reply['code']);
		}

		// say hello
		if ($this->_command('EHLO') === false)
		{
			return false;
		}
	}

	/**
	 * Disconnect from SMTP server
	 *
	 * @since version 1.0.0
	 */
	private function _disconnect()
	{
		if (is_resource($this->_connection) !== false)
		{
			$this->_command('QUIT');

			// only disconnect stream if the above QUIT didn't kill it
			if (is_resource($this->_connection) !== false)
			{
				fclose($this->_connection);
			}

			// make sure it's not a resource anymore
			$this->_connection = null;
		}
	}

	/**
	 * Takes any address and extracts the email address
	 * For example:
	 * John Doe <johdo@domain.com>
	 * The return would be johdo@domain.com
	 *
	 * @param string $address
	 * @return string
	 * @since version 1.0.0
	 */
	private function _extractAddress($address)
	{
		// since address can be given in FULL format we MUST
		// strip them from it and give just the address part
		$matches = array();
		if (preg_match('!(?:[^<]*<)?(.+?@.+?\.[^>]+)>?!i', $address, $matches))
		{
			if (!empty($matches[1]))
			{
				return $matches[1];
			}
		}

		return '';
	}

	/**
	 * Gets the error code's message
	 *
	 * @param string $code
	 * @return string
	 * @since version 1.0.0
	 */
	private function _getCodeMsg($code)
	{
		switch ($code)
		{
			case self::SYSTEM_STATUS:
				return '211 System status, or system help reply';
			case self::HELP_MESSAGE:
				return '214 Help message';
			case self::SERVICE_READY:
				return '220 <domain> Service ready';
			case self::SERVICE_CLOSING:
				return '221 <domain> Service closing transmission channel';
			case self::OK:
				return '250 Requested mail action okay, completed';
			case self::USER_NOT_LOCAL_WILL_FORWARD:
				return '251 User not local; will forward to <forward-path>';
			case self::CANT_VRFY_USER:
				return '252 Cannot VRFY user, but will accept message and attempt delivery';
			case self::START_MAIL_INPUT:
				return '354 Start mail input; end with <CRLF>.<CRLF>';
			case self::SERVICE_NOT_AVAILABLE;
				return '421 <domain> Service not available, closing transmission channel';
			case self::MAILBOX_UNAVAILABLE_BUSY;
				return '450 Requested mail action not taken: mailbox unavailable';
			case self::LOCAL_ERROR;
				return '451 Requested action aborted: local error in processing';
			case self::INSUFFICIENT_SYSTEM_STORAGE;
				return '452 Requested action not taken: insufficient system storage';
			case self::SE_COMMAND_UNRECOGNIZED;
				return '500 Syntax error, command unrecognized';
			case self::SE_PARAMETERS_ARGUMENTS;
				return '501 Syntax error in parameters or arguments';
			case self::COMMAND_NOT_IMPLEMENTED;
				return '502 Command not implemented';
			case self::BAD_SEQUENCE;
				return '503 Bad sequence of commands';
			case self::COMMAND_PARMS_NOT_IMPLEMENTED;
				return '504 Command parameter not implemented';
			case self::MAILBOX_UNAVAILABLE;
				return '550 Requested action not taken: mailbox unavailable';
			case self::USER_NOT_LOCAL_NO_FORWARD;
				return '551 User not local; please try <forward-path>';
			case self::EXCEEDED_STORAGE;
				return '552 Requested mail action aborted: exceeded storage allocation';
			case self::MAILBOX_NAME_NOT_ALLOWED;
				return '553 Requested action not taken: mailbox name not allowed';
			case self::TRANSACTION_FAILED;
				return '554 Transaction failed  (Or, in the case of a connection-opening';
			default:
				return '';
		}
	}

	/**
	 * Read one line at a time
	 *
	 * if $checkCode is a number, the replies WILL loop until a that code
	 * is received, or until the end of the stream
	 *
	 * Returns an array containing 2 items
	 * reply
	 * code
	 *
	 * if $tillend is true then it will get all replies till the end of the
	 * stream, it WILL return the last item.
	 *
	 * @return array
	 * @param integer $checkCode [optional]
	 * @param bool $tillend [optional]
	 * @since version 1.0.0
	 */
	private function _getLine($checkCode = '', $tillend = false)
	{
		// clear them
		$reply = '';
		$code = 0;

		do
		{
			// get the line from the stream
			$reply = trim(fgets($this->_connection, self::LINE));

			// check for invalid stream
			if ($reply === false)
			{
				throw new SmtpException('Could not read from stream',
					SmtpException::CONNECTION_ERROR);
			}

			// log the replies
			$this->_rawCR[] = 'Reply: '.$reply;

			$len = strlen($reply);

			// get the reply code
			$code = 0;
			if ($len > 0)
			{
				$pos = strpos($reply, ' ');
				if ($pos !== false)
				{
					$code = substr(trim($reply), 0, $pos);
				}
				else
				{
					$pos = strpos($reply, '-');
					$code = substr(trim($reply), 0, $pos);
				}
			}

			// no more data always break;
			if ($len <= 0)
			{
				break;
			}

			// no checkReply or checkCode is given then we break out
			if (empty($checkCode))
			{
				break;
			}
			// check against code
			elseif (!empty($checkCode) && $checkCode == $code)
			{
				break;
			}
		}
		while (true);

		return array(
			'reply' => $reply,
			'code' => $code
		);
	}

	/**
	 * Write Line to stream
	 * Returns amount of characters written
	 *
	 * @param string $line
	 * @return integer
	 * @since version 1.0.0
	 */
	private function _writeLine($line)
	{
		// always clear the connection before sending a message
		$this->_clearConnection();

		// write to stream
		$reply = fwrite($this->_connection, $line.self::CRLF);

		// log the raw commands
		$this->_rawCR[] = 'Sent: '.$line;

		if ($reply === false)
		{
			throw new SmtpException('Could not write to stream',
				SmtpException::CONNECTION_ERROR);
		}

		// return amount of characters written
		return $reply;
	}
}

?>
