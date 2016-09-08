<?php

/**
 * @author Ingo Schommer, SilverStripe Ltd. (<firstname>@silverstripe.com)
 * @package testing
 * 
 * @todo Test custom tags
 */
class EmailImportTest extends FunctionalTest
{
    protected static $email_file = "email-manager/tests/TestEmail.txt";

    /**
    * Test that our import data is accurate
    */
    public function testImport()
    {
        $email_string = file_get_contents(BASE_PATH . "/" . self::$email_file, true);
        $email = EmailMessage::create();
        $email->import($email_string);

        // Test we have got the data properly
        $this->assertEquals("<CAMMBQR4HLgG+dxSnK-3Vq6XPt7WKjq41J9xQ052Y5oM9hwM9_Q@mail.gmail.com>", $email->MessageID);
        $this->assertEquals("Re: Test", $email->Subject);
        $this->assertEquals("Sender <sender@ilateral.co.uk>", $email->From);
        $this->assertEquals("Reciever <reciever@ilateral.co.uk>", $email->To);
        $this->assertEquals("Test reply with name Siôn Simon", trim($email->getBody()));
        $this->assertEquals("", $email->getHTMLBody());
    }
}