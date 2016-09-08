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

    protected static $standard_forward_file = "email-manager/tests/TestEmailForward_Standard.txt";

    protected static $ipad_forward_file = "email-manager/tests/TestEmailForward_Ipad.txt";

    protected static $simple_forward_file = "email-manager/tests/TestEmailForward_Simple.txt";

    protected static $outlook_forward_file = "email-manager/tests/TestEmailForward_Outlook.txt";

    /**
    * Test that our import data is accurate
    */
    public function testImport()
    {
        $email_string = file_get_contents(BASE_PATH . "/" . self::$email_file, true);
        $email = EmailMessage::create();
        $email->import($email_string);

        // Test we have got the data properly
        $this->assertEquals("CAMMBQR4HLgG+dxSnK-3Vq6XPt7WKjq41J9xQ052Y5oM9hwM9_Q@mail.gmail.com", $email->MessageID);
        $this->assertEquals("Re: Test", $email->Subject);
        $this->assertEquals("sender@ilateral.co.uk", $email->FromEmail);
        $this->assertEquals("Sender", $email->FromName);
        $this->assertEquals("reciever@ilateral.co.uk", $email->ToEmail);
        $this->assertEquals("Reciever", $email->ToName);
        $this->assertEquals(trim("Test reply with name Siôn Simon<br />"), trim(utf8_decode($email->Body)));
    }

    /**
    * Test that our import data is accurate
    */
    public function testImportFWD() {
        $email_string = file_get_contents(BASE_PATH . "/" . self::$standard_forward_file, true);
        $email = EmailMessage::create();
        $email->import($email_string);

        // Test we have got the data properly
        $this->assertEquals("54C9FF63.903@gmail.com", $email->MessageID);
        $this->assertEquals("Fwd: Test Email", $email->Subject);
        $this->assertEquals("origin@ilateral.co.uk", $email->FromEmail);
        $this->assertEquals("Origin", $email->FromName);
        $this->assertEquals("reciever@ilateral.co.uk", $email->ToEmail);
        $this->assertEquals("Reciever", $email->ToName);
    }

    /**
    * Test that our import data is accurate
    */
    public function testImportIpadFWD() {
        $email_string = file_get_contents(BASE_PATH . "/" . self::$ipad_forward_file, true);
        $email = EmailMessage::create();
        $email->import($email_string);

        // Test we have got the data properly
        $this->assertEquals("54C9FF63.903@gmail.com", $email->MessageID);
        $this->assertEquals("Fwd: Test Email", $email->Subject);
        $this->assertEquals("origin@ilateral.co.uk", $email->FromEmail);
        $this->assertEquals("Origin Sender", $email->FromName);
        $this->assertEquals("reciever@ilateral.co.uk", $email->ToEmail);
        $this->assertEquals("Reciever", $email->ToName);
    }

    /**
    * Test that our import data is accurate
    */
    public function testImportOutlookFWD() {
        $email_string = file_get_contents(BASE_PATH . "/" . self::$outlook_forward_file, true);
        $email = EmailMessage::create();
        $email->import($email_string);

        // Test we have got the data properly
        $this->assertEquals("54C9FF63.903@gmail.com", $email->MessageID);
        $this->assertEquals("FW: Test Email", $email->Subject);
        $this->assertEquals("origin@ilateral.co.uk", $email->FromEmail);
        $this->assertEquals("Origin", $email->FromName);
        $this->assertEquals("reciever@ilateral.co.uk", $email->ToEmail);
        $this->assertEquals("Reciever", $email->ToName);
    }

    /**
    * Test that our import data is accurate
    */
    public function testImportSimpleFWD() {
        $email_string = file_get_contents(BASE_PATH . "/" . self::$simple_forward_file, true);
        $email = EmailMessage::create();
        $email->import($email_string);

        // Test we have got the data properly
        $this->assertEquals("54C9FF63.903@gmail.com", $email->MessageID);
        $this->assertEquals("Fwd: Test Email", $email->Subject);
        $this->assertEquals("origin@ilateral.co.uk", $email->FromEmail);
        $this->assertEquals("", $email->FromName);
        $this->assertEquals("reciever@ilateral.co.uk", $email->ToEmail);
        $this->assertEquals("Reciever", $email->ToName);
    }
}