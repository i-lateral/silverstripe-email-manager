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

    public function getParsedEmail()
    {
        $email_string = file_get_contents(BASE_PATH . "/" . self::$email_file, true);
        $email = EmailMessage::create();
        $email->import($email_string);
        return $email;
    }

    /**
    * Test that our standard import data is accurate
    */
    public function testImport()
    {
        $email = $this->getParsedEmail();

        $this->assertEquals("<CAMMBQR4HLgG+dxSnK-3Vq6XPt7WKjq41J9xQ052Y5oM9hwM9_Q@mail.gmail.com>", $email->MessageID);
        $this->assertEquals("Re: Test", $email->Subject);
        $this->assertEquals("Sender <sender@ilateral.co.uk>", $email->From);
        $this->assertEquals("Reciever <reciever@ilateral.co.uk>", $email->To);
    }

    /**
    * Test that after import we can get the email body
    */
    public function testEmailFromString()
    {
        $email = $this->getParsedEmail();
        $matches = EmailMessage::get_emails_from_string($email->From);
        
        $this->assertEquals("sender@ilateral.co.uk", $matches[0]);
    }

    /**
    * Test that after import we can get the email body
    */
    public function testEmailBody()
    {
        $email = $this->getParsedEmail();

        $this->assertEquals("Test reply with name Siôn Simon", trim($email->getBody()));
    }

    /**
    * Test that after import we can get some html
    */
    public function testEmailHTML()
    {
        $email = $this->getParsedEmail();

        $this->assertContains("<p>Test reply with name Siôn Simon</p>", $email->getHTMLBody());
    }

    /**
    * Test sent date is set correctly
    */
    public function testEmailSent()
    {
        $email = $this->getParsedEmail();
        $date = $email->dbobject("Sent");

        $this->assertEquals(2014, $date->Year());
        $this->assertEquals("December", $date->Month());
        $this->assertEquals(2, $date->DayOfMonth());
    }
}